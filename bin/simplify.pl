/**********************************************************************
% Copyright (C) 1997 Ullrich Hustadt
%                    Max-Planck-Institut fuer Informatik
%                    Im Stadtwald
%                    66123 Saarbruecken, Germany

% simplify.pl is free software; you can redistribute it and/or modify
% it under the terms of the GNU General Public License as published by
% the Free Software Foundation; either version 1, or (at your option)
% any later version.

% simplify.pl is distributed in the hope that it will be useful,
% but WITHOUT ANY WARRANTY; without even the implied warranty of
% MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
% GNU General Public License for more details.
**********************************************************************/

%:- use_module(library(lists)).
%:- use_module(library(ordsets)).

% simplify_alc(+TERM1,-TERM2)
% transform TERM1 to negation normal form and simplify it according to 
% the rules of Table 1 in the MPII Research report 97-2-003 by Ullrich 
% Hustadt and Renate A. Schmidt.

simplify_alc(Term1,Term3) :-
	nnf_term(Term1,Term0),
	simplify_term(Term0,nnf,Term2),
	(getArgs(Term2,and,L2) ->
	    subsumption(L2,L3),
	    (sortOpt([conjunctions|_]) ->
		add_num(L3,L4),
		keysort(L4,L5),
		del_num(L5,L6)
	    ;
		L6 = L3
	    ),
	    rebuild_conjunction(L6,Term3)
	;
	    Term3 = Term2
	).

flatten_term(not(F1),Op,not(F2)) :-
	flatten_term(F1,Op,F2).
flatten_term(box(R1,F1),Op,box(R1,F2)) :-	
	flatten_term(F1,Op,F2).
flatten_term(dia(R1,F1),Op,dia(R1,F2)) :-
	flatten_term(F1,Op,F2).
flatten_term(implies(T1,T2),Op,T3) :-
	flatten_term(or(not(T1),T2),Op,T3).
flatten_term(equiv(T1,T2),Op,T3) :-
	flatten_term(and(or(not(T1),T2),or(not(T2),T1)),Op,T3).
flatten_term(T1,_Op,T1) :-
	atomic(T1).
flatten_term(F1,_Op,T2) :-
	(getArgs(F1,and,Args1) ->
	    flatten_terms(Args1,and,T2)
	;
	    (getArgs(F1,or,Args1) ->
		flatten_terms(Args1,or,T2)
	    ;
		T2 = F1
	    )
	), !.
		
flatten_terms(Args1,Op,F2) :-
	flatten_terms1(Args1,Op,Args2),
	construct_term(Args2,Op,F2).

construct_term([],and,false) :- !.
construct_term([],or,true) :-   !.
construct_term([F1],_Op,F1) :-   !.
construct_term(FL,Op,T1) :-     T1 =.. [Op|FL], !.

flatten_terms1([],_Op,[]).
flatten_terms1([F1|FL1],Op,FL4) :-
	flatten_terms1(FL1,Op,FL2),
	flatten_term(F1,Op,F3),
	(functor(F3,Op,_) ->
	    getArgs(F3,Op,FL3)
	;
	    FL3 = [F3]
	),
	append(FL3,FL2,FL4).

getArgs(Term,Op,Args) :-
	Term =.. [Op|Args],
	!.

nnf_term(not(false),true) :- !.
nnf_term(not(true),false) :- !.
nnf_term(not(not(T1)),T2) :-
	!,
	nnf_term(T1,T2).
nnf_term(implies(T1,T2),T3) :-
	nnf_term(or(not(T1),T2),T3).
nnf_term(equiv(T1,T2),T3) :-
	nnf_term(and(or(not(T1),T2),or(not(T2),T1)),T3).
nnf_term(not(box(R1,T1)),dia(R1,T2)) :-
	!,
	nnf_term(not(T1),T2).
nnf_term(not(dia(R1,T1)),box(R1,T2)) :-
	!,
	nnf_term(not(T1),T2).
nnf_term(not(F1),F4) :-
	functor(F1,and,_),
	!,
	flatten_term(F1,and,F2),
	(F2 =.. [and|L1] ->
	    nnf_not_terms(L1,L2),
	    construct_term(L2,or,F3),
	    flatten_term(F3,_,F4)
	;
	    nnf_term(not(F2),F3),
	    flatten_term(F3,_,F4)
	).
nnf_term(not(F1),F4) :-
	functor(F1,or,_),
	!,
	flatten_term(F1,or,F2),
	(F2 =.. [or|L1] ->
	    nnf_not_terms(L1,L2),
	    construct_term(L2,and,F3),
	    flatten_term(F3,_,F4)
	;
	    nnf_term(not(F2),F3),
	    flatten_term(F3,_,F4)
	).
nnf_term(not(implies(F1,F2)),F3) :-
	!,
	nnf_term(and(F1,not(F2)),F3).
nnf_term(T1,T1) :-
	atomic(T1),
	!.
nnf_term(not(T1),not(T1)) :-
	atomic(T1),
	!.
nnf_term(T1,T3) :-
	getArgs(T1,and,Args1),
	!,
	nnf_terms(Args1,NArgs1),
	construct_term(NArgs1,and,T2),
	flatten_term(T2,_,T3),
	!.
nnf_term(T1,T3) :-
	getArgs(T1,or,Args1),
	!,
	nnf_terms(Args1,NArgs1),
	construct_term(NArgs1,or,T2),
	flatten_term(T2,_,T3),
	!.
nnf_term(T1,T2) :-
	functor(T1,F,N),
	functor(T2,F,N),
	nnf_args(N,T1,T2).

nnf_terms([],[]).
nnf_terms([T1|TL1],[T2|TL2]) :-
	nnf_term(T1,T2),
	nnf_terms(TL1,TL2).


nnf_not_terms([],[]).
nnf_not_terms([T1|TL1],[T2|TL2]) :-
	nnf_term(not(T1),T2),
	nnf_not_terms(TL1,TL2).

nnf_args(1,T1,T2) :-
	arg(1,T1,A1),
	nnf_term(A1,A2),
	arg(1,T2,A2),
	!.
nnf_args(N,T1,T2) :-
	N >= 2,
	arg(N,T1,A1),
	nnf_term(A1,A2),
	arg(N,T2,A2),
	M is N-1,
	nnf_args(M,T1,T2),
	!.	


num_symbols(box(_R,C),N) :-
	num_symbols(C,M),
	N is M+7.
num_symbols(dia(_R,C),N) :-
	num_symbols(C,M),
	N is M+7.
num_symbols(not(C),N) :-
	num_symbols(C,M),
	N is M+1.
num_symbols(F1,N) :-
	getArgs(F1,and,L),
	!,
	num_symbols_list(L,N).
num_symbols(F1,N) :-
	getArgs(F1,or,L),
	!,
	num_symbols_list(L,N).
num_symbols(C,1) :-
	atomic(C),
	name(C,[114,112|_]),
	!.
num_symbols(C,2) :-
	atomic(C).
num_symbols([L1],N) :-
	num_symbols(L1,N).
num_symbols_list([],0).
num_symbols_list([C1|CL],N) :-
	num_symbols(C1,N1),
	num_symbols_list(CL,NL),
	N is N1 + NL.

add_num([],[]).
add_num([C1|CL],[N1-C1|CLX]) :-
	num_symbols(C1,N1),
	add_num(CL,CLX).
del_num([],[]).
del_num([_-C1|CLX],[C1|CL]) :-
	del_num(CLX,CL).


simplify_term(not(T1),NegType,T2) :-
	!,
	simplify_term(T1,NegType,T3),
	(NegType == nnf ->
	    negate_term(T3,T2)
	;
	    (atomic(T3) ->
		negate_term(T3,T2)
	    ;
		T2 = not(T3)
	    )
	).
simplify_term(box(R,T1),NegType,T3) :-
	!,
	simplify_term(T1,NegType,T2),
	rebuild_box(T2,R,T3).
simplify_term(dia(R,T1),NegType,T3) :-
	!,
	simplify_term(T1,NegType,T2),
	rebuild_dia(T2,R,T3).
simplify_term(T1,_NegType,T1) :-
	atomic(T1),
	!.
simplify_term(F1,NegType,T2) :-
	(functor(F1,and,_) ->
	    getArgs(F1,and,L1),
	    !,
	    simplify_conjunction(L1,NegType,T2)
	;
	    getArgs(F1,or,L1),
	    !,
	    simplify_disjunction(L1,NegType,T2)
	).

rebuild_box(true,_,true).
rebuild_box(false,R,box(R,false)).
rebuild_box(T1,R,box(R,T1)).

rebuild_dia(false,_,false).
rebuild_dia(true,R,dia(R,true)).
rebuild_dia(T1,R,dia(R,T1)).

negate_term(box(_,true),false).
negate_term(not(T1),T1).
negate_term(box(R,T1),dia(R,T2)) :-
	negate_term(T1,T2).
negate_term(dia(R,T1),box(R,T2)) :-
        negate_term(T1,T2).
negate_term(true,false).
negate_term(false,true).
negate_term(F1,F2) :-
	getArgs(F1,and,L1),
	!,
	negate_terms(L1,L2),
	F2 =.. [or|L2].
negate_term(F1,F2) :-
	getArgs(F1,or,L1),
	!,
	negate_terms(L1,L2),
	F2 =.. [and|L2].
negate_term(T1,not(T1)).

negate_terms([],[]).
negate_terms([T1|TL1],[T2|TL2]) :-
	negate_term(T1,T2),
	negate_terms(TL1,TL2).
	

simplify_elements_of_list([],_NegType,[]) :-
	!.
simplify_elements_of_list([T1|TL1],NegType,[T2|TL2]) :-
	simplify_term(T1,NegType,T2),
	simplify_elements_of_list(TL1,NegType,TL2).

simplify_conjunction(L1,NegType,T3) :-
	simplify_elements_of_list(L1,NegType,L2),
	(member(false,L2) ->
	    T3 = false
	;
	    (sortOpt([conjunctions|_]) -> 
		list_to_ord_set(L2,L3)
	    ;
		L3 = L2
	    ),
	    simplify_conjunctive_list(L3,L4),
	    rebuild_conjunction(L4,T3)
	).

rebuild_conjunction([],true).
rebuild_conjunction([T1],T1) :-
	!.
rebuild_conjunction(TL,Term) :-
	Term =.. [and|TL],
	!.

subsumption([not(T1)|TL1],[not(T1)|TL4]) :-
	atomic(T1),
	eliminate_subsumed_disjunctions(TL1,not(T1),TL2),
	eliminate_from_disjunctions(TL2,T1,TL3),
	subsumption(TL3,TL4).
subsumption([T1|TL1],[T1|TL4]) :-
	atomic(T1),
	eliminate_subsumed_disjunctions(TL1,T1,TL2),
	eliminate_from_disjunctions(TL2,not(T1),TL3),
	subsumption(TL3,TL4).
subsumption([T1|TL1],[T1|TL2]) :-
	subsumption(TL1,TL2).
subsumption([],[]).

eliminate_subsumed_disjunctions([],_,[]).
eliminate_subsumed_disjunctions([or(L1)|TL1],T1,TL2) :-
	member(T1,L1),
	eliminate_subsumed_disjunctions(TL1,T1,TL2).
eliminate_subsumed_disjunctions([T1|TL1],T2,[T1|TL2]) :-
	eliminate_subsumed_disjunctions(TL1,T2,TL2).

eliminate_from_disjunctions([],_,[]).
eliminate_from_disjunctions([or(L1)|TL1],T1,[T2|TL2]) :-
	!,
	delete(L1,T1,L2),
	(L2 = [T3] ->
	    T2 = T3
	;
	    T2 = or(L2)
	),
	eliminate_from_disjunctions(TL1,T1,TL2).
eliminate_from_disjunctions([T1|TL1],T2,[T1|TL2]) :-
	eliminate_from_disjunctions(TL1,T2,TL2).
	
simplify_disjunction(L1,NegType,T3) :-
	simplify_elements_of_list(L1,NegType,L2),
	(member(true,L2) ->
	    T3 = true
	;
	    (sortOpt([_,disjunctions|_]) -> 
		list_to_ord_set(L2,L3)
	    ;
		(sortOpt([_,weight_disjunctions|_]) ->
		    add_num(L2,LH1),
		    keysort(LH1,LH2),
		    del_num(LH2,L3)
		;
		    L3 = L2
		)
	    ),
	    simplify_disjunctive_list(L3,L4),
	    rebuild_disjunction(L4,T3)
	).	

rebuild_disjunction([],false).
rebuild_disjunction([T1],T1) :-
	!.
rebuild_disjunction(TL,Term) :-
	Term =.. [or|TL],
	!.

% simplify_conjunctive_list(LISTE1,LISTE2)
% Beachte: Die Eingabeliste LISTE1 kann die Konstante `false' nicht
% enthalten.
simplify_conjunctive_list([],[]) :-
	!.
simplify_conjunctive_list([true|TL1],TL2) :-
	!,
	simplify_conjunctive_list(TL1,TL2).
simplify_conjunctive_list([not(C1)|TL1],TL2) :-
	(member(C1,TL1) ->
	    TL2 = [false]
	;
	    simplify_conjunctive_list(TL1,TL3),
	    (TL3 = [false] ->
		TL2 = [false]
	    ;
		(member(not(C1),TL3) ->
		    TL2 = TL3
		;
		    TL2 = [not(C1)|TL3]
		)
	    )
	).
simplify_conjunctive_list([C1|TL1],TL2) :-
	(member(not(C1),TL1) ->
	    TL2 = [false]
	;
	    simplify_conjunctive_list(TL1,TL3),
	    (TL3 = [false] ->
		TL2 = [false]
	    ;
		(member(C1,TL3) ->
		    TL2 = TL3
		;
		    TL2 = [C1|TL3]
		)
	    )
	).

% simplify_disjunctive_list(LISTE1,LISTE2)
% Beachte: Die Eingabeliste LISTE1 kann die Konstante `true' nicht
% enthalten.
simplify_disjunctive_list([],[]) :-
	!.
simplify_disjunctive_list([false|TL1],TL2) :-
	!,
	simplify_disjunctive_list(TL1,TL2).
simplify_disjunctive_list([not(C1)|TL1],TL2) :-
	(member(C1,TL1) ->
	    TL2 = [true]
	;
	    simplify_disjunctive_list(TL1,TL3),
	    (TL3 = [true] ->
		TL2 = [true]
	    ;
		(member(not(C1),TL3) ->
		    TL2 = TL3
		;
		    TL2 = [not(C1)|TL3]
		)
	    )
	).
simplify_disjunctive_list([box(R,F)|TL1],TL2) :-
	!,
	nnf_term(dia(R,not(F)),C1),
	(member(C1,TL1) ->
	    TL2 = [true]
	;
	    simplify_disjunctive_list(TL1,TL3),
	    (TL3 = [true] ->
		TL2 = [true]
	    ;
		(member(dia(R,true),TL3) ->
		    TL2 = [true]
		;
		    (member(box(R,F),TL3) ->
			TL2 = TL3
		    ;
			TL2 = [box(R,F)|TL3]
		    )
		)
	    )
	).
simplify_disjunctive_list([dia(R,true)|TL1],TL2) :-
	!,
	(member(box(R,_),TL1) ->
	    TL2 = [true] 
	;
	    simplify_disjunctive_list(TL1,TL3),
	    (TL3 = [true] ->
		TL2 = [true]
	    ;
		TL2 = [dia(R,true)|TL3]
	    )
	).
simplify_disjunctive_list([dia(R,F)|TL1],TL2) :-
	!,
	nnf_term(box(R,not(F)),C1),
	(member(C1,TL1) ->
	    TL2 = [true]
	;
	    simplify_disjunctive_list(TL1,TL3),
	    (TL3 = [true] ->
		TL2 = [true]
	    ;
		(member(dia(R,F),TL3) ->
		    TL2 = TL3
		;
		    TL2 = [dia(R,F)|TL3]
		)
	    )
	).
simplify_disjunctive_list([C1|TL1],TL2) :-
	(member(not(C1),TL1) ->
	    TL2 = [true]
	;
	    simplify_disjunctive_list(TL1,TL3),
	    (TL3 = [true] ->
		TL2 = [true]
	    ;
		(member(C1,TL3) ->
		    TL2 = TL3
		;
		    TL2 = [C1|TL3]
		)
	    )
	).

	

	
new_predicate(Prefix,Symbol) :-
                ( retract(new_predicate_counter(Prefix,Value)) ; Value = 100 ),
                NewValue is Value+1,
                asserta(new_predicate_counter(Prefix,NewValue)),
                name(Prefix,P),
                name(NewValue,N),
                append(P,N,SymbolList),
                name(Symbol,SymbolList), !.

get_units([],[]) :-
	!.
get_units([and(_)|L1],L2) :-
	!,
	get_units(L1,L2).
get_units([or([A])|L1],L3) :-
	atomic(A),
	!,
	get_units(L1,L2),
	ord_union([A],L2,L3).
get_units([or([not(B)])|L1],L3) :-
	atomic(B),
	!,
	get_units(L1,L2),
	ord_union([not(B)],L2,L3).
get_units([or([_|_])|L1],L2) :-
	!,
	get_units(L1,L2).
get_units([A|L1],L3) :-
	atomic(A),
	!,
	get_units(L1,L2),
	ord_union([A],L2,L3).
get_units([not(B)|L1],L3) :-
	atomic(B),
	!,
	get_units(L1,L2),
	ord_union([not(B)],L2,L3).
get_units([_|L1],L2) :-
	!,
	get_units(L1,L2).

unit_propagate(false,false) :- 
	!.
unit_propagate(true,true) :-
	!.
unit_propagate(L1,L2) :-
	get_units(L1,Units),
	(Units == [] ->
	    L2 = L1
	;
	    (member(false,Units) ->
		!,
		L2 = false
	    ;
		propagate(L1,Units,L3),
		unit_propagate(L3,L4),
		(L4 == [] ->
		    L2 = true
		;
		    (atomic(L4) ->
			L2 = L4
		    ;
			append(L4,Units,L2)
		    )
		)
	    )
	),
	!.

% propagate(L1,Units,L2)
% L2 ist das Ergebnis von unit propagation mit Units auf L1.
propagate([],_,[]) :-
	!.
propagate([Disj1|L1],Units,L3) :-
	propagate1(Units,Disj1,Disj2),
	(Disj2 == false ->
	    L3 = false
	;
	    propagate(L1,Units,L2),
	    (Disj2 == true ->
		L3 = L2
	    ;
		(L2 == false ->
		    L3 = false
		;
		    L3 = [Disj2|L2]
		)
	    )
	),
	!.

% propagate1(Units,L1,L2)
% L2 ist das Ergebnis von unit propagation mit Units auf L1.
propagate1([],Disj,Disj) :-
	!.
propagate1([Unit|UL],Disj1,Disj3) :-
	(Disj1 = or(DL1) ->
	    propagate2(Unit,DL1,Disj2)
	;
	    propagate2(Unit,[Disj1],Disj2)
	),
	(Disj2 == true ->
	    Disj3 = true
	; 
	    (Disj2 == false ->
		Disj3 = false
	    ;
		propagate1(UL,Disj2,Disj3)
	    )
	),
	!.

propagate2(Unit,DL,Disj2) :-
	(member(Unit,DL) ->
	    Disj2 = true
	;
	    nnf_term(not(Unit),Neg),
	    delete(DL,Neg,Res),
	    (Res = [] ->
		Disj2 = false
	    ;
		(Res == false ->
		    Disj2 = false
		;
		    Disj2 = or(Res)
		)
	    )
	),
	!.
		
