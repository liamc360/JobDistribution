#!/usr/bin/swipl -L16g -G16g

/**********************************************************************
% Copyright (C) 2000 Ullrich Hustadt
%                    Department of Computing and Mathematics
%                    Chester Street, Manchester M1 5GD
%                    United Kingdom

% tancs.pl is free software; you can redistribute it and/or modify
% it under the terms of the GNU General Public License as published by
% the Free Software Foundation; either version 1, or (at your option)
% any later version.

% tancs.pl is distributed in the hope that it will be useful,
% but WITHOUT ANY WARRANTY; without even the implied warranty of
% MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
% GNU General Public License for more details.
**********************************************************************/

:- set_prolog_flag(verbose, silent).

:- initialization main.

main :-
    current_prolog_flag(argv, [FileIn,FileOut]),
    format('Transforming ~a to ~a~n', [FileIn,FileOut]),
    transformFile(FileIn,FileOut),
    halt.
main :-
    halt(1).

sortOpt([]).
%simplifyOpt([purify]).
simplifyOpt([]).

:- consult('simplify.pl').

%% To generate a stand-alone executable `tancs' use
% :- qsave_program(tancs,[goal(main),stand_alone(true),global(32000),local(20000),trail(10000)]).
%% `tancs' will take two file names as parameters:
%% The first file should contain a problem description in TANCS syntax
%% and the second file will contain its translation to MSPASS syntax
%% after `tancs' has finished.
%% The name of the program and the two parameter have to separated by
%% `--'. So, a typical call has the form
%% [prompt] tancs -- TANCS_File MSPASS_File

%% Path to a directory to which we can write temporary files
%% Note: The path has to end with a '/'.

create_tmp :-
  (exists_directory('/LOCAL/ullrich/tmp/') ->
 	 true
	;
 	 make_directory('/LOCAL/ullrich/tmp/')
  ).
%:- create_tmp.

%% Complete path of MSPASS.

spass_binary('/users/lect/ullrich/provers/MSPASS-1.0.0t.1.3/SPASS').

%% Some predicates which might differ from Prolog dialect to Prolog dialect
%% SWI-Prolog has build-in predicates list_to_set and union, print/1 acts
%% differently to print/1 in SICStus Prolog, and statistics knows only 
%% cputime (not runtime).
%% In case SICStus Prolog is used instead of 

listToSet(L1,S1) :-
	list_to_set(L1,S1),
	!.
setUnion(S1,S2,S3) :-
	union(S1,S2,S3),
	!.
getRuntime(RT) :-
	statistics(cputime,RT).

printOut(not(F1)) :-
	write('(not '), printOut(F1), write(')'), !.
printOut(or(F1,F2)) :-
	write('(or '), printOut(F1), write(' '), printOut(F2), write(')'), !.
printOut(and(F1,F2)) :-
	write('(and '), printOut(F1), write(' '), printOut(F2), write(')'), !.
printOut(implies(F1,F2)) :-
	write('(or (not '), printOut(F1), write(') '), printOut(F2), write(')'), !.
printOut(equiv(F1,F2)) :-
	write('(and '),
	printOut(implies(F1,F2)),
	printOut(implies(F2,F1)),
	write(')'), !.
printOut(box(_R,F1)) :-
	write('(all R0 '), printOut(F1), write(')'), !.
printOut(dia(_R,F1)) :-
	write('(some R0 '), printOut(F1), write(')'), !.
printOut(false) :-
	write('*BOT*'), !.
printOut(true) :-
	write('*TOP*'), !.
printOut(F1) :-
	atomic(F1),
	atom_chars(F1,[_|N]),
	atom_chars(F2,['C'|N]),
	write(F2),
	!.
printOut(S) :-
	write_term(S,[quoted(false),ignore_ops(false),portrayed(true)]).


% TANCS formula syntax:
%
% fml ::= true
%         false
%         vN
%         (fml)
%         ~fml
%         fml & fml
%         fml | fml 
%         fml => fml
%         fml <= fml
%         fml <=> fml
%         fml <~> fml
%         box rel : fml
%         pos rel :fml
% 
% rel ::= rN
%         (rel)
%         rel-
%         rel*
%         rel & rel
%         rel | rel
%         rel # rel
% 
:- op(300,xfx,':').
:- op(150,fy,box).
:- op(150,fy,br1).
:- op(150,fy,pos).
:- op(150,fy,dr1).
:- op(330,yfx,'\\/').
:- op(320,yfx,'&').
:- op(230,xfy,'-').
:- op(240,xfy,'#').
:- op(150,fy,'neg').
:- op(220,yf,'*').
:- op(340,xfy,'=>').
:- op(340,xfy,'<=>').


%main :-
%	unix(argv(Argv)),
%	append(_PrologArgs,[--|AppArgs],Argv),
%	runProblem(AppArgs).

transformFile(FileIn,FileOut) :-
	readProblem(FileIn,IFList),
	transSyntax(IFList,AxList,ConjList,_PropSym,_RelSym),
	tell(FileOut),
	(simplifyOpt([]) ->
	    ConjListS = ConjList
	;
	    (AxList == [] ->
		simplList(ConjList,ConjListS)
	    ;
		ConjListS = ConjList
	    )
	),
        (AxList == [] ->
            true
        ;
	    printOut('list_of_special_formulae(axioms, eml).'), nl,
            printAxioms(AxList),
	    printOut('end_of_list.'), nl, nl
        ),
	(ConjList == [] ->
	    true
	;
	    write('(defconcept D0 '),
	    printConjecture(ConjListS),
	    write(')'),
	    nl
	),
        told,
	!.

createFiles(FileIn,FileOut) :-
	readProblem(FileIn,IFList),
	transSyntax(IFList,AxList,ConjList,PropSym,RelSym),
	tell(FileOut),
	append(PropSym,RelSym,PredSyms),
	printPreamble(PredSyms),
	(simplifyOpt([]) ->
	    ConjListS = ConjList
	;
	    (AxList == [] ->
		simplList(ConjList,ConjListS)
	    ;
		ConjListS = ConjList
	    )
	),
        (AxList == [] ->
            true
        ;
	    printOut('list_of_special_formulae(axioms, eml).'), nl,
            printAxioms(AxList),
	    printOut('end_of_list.'), nl, nl
        ),
	(ConjList == [] ->
	    true
	;
	    printConjecture(ConjListS),
	    nl
	),
        printOut('list_of_settings(SPASS).'), nl,
        printOut('{*'), nl,
	printFlags,
        printOut('*}'), nl,
        printOut('end_of_list.'), nl,
        printOut('end_problem.'), nl, nl,
        told,
	!.


printAxioms([]) :-
	!.
printAxioms([F1|FL]) :-
	printOut('prop_formula('),
	printOut(F1),
	printOut(').'),
	nl,
	printAxioms(FL).

printConjecture([]) :-
	!.
printConjecture([F1]) :-
	printOut(F1).
printConjecture([F1,F2|FL]) :-
	printOut(F1),
	nl,
	printConjecture([F2|FL]).

sortPredSyms(L0,DL,CL) :-
        atom_chars(d,[D]),
        getDefPreds(L0,D,DL,CL).

getDefPreds([],_,[],[]).
getDefPreds([S|SL],D,L3,L4) :-
        getDefPreds(SL,D,L1,L2),
        (atom_chars(S,[D|_]) ->
            L3 = [S|L1],
            L4 = L2
        ;
            L3 = L1,
            L4 = [S|L2]
        ).

printPrecedence([],_).
printPrecedence([S],N) :-
        printOut('('), printOut(S), printOut(','), printOut(N), printOut(')').
printPrecedence([S1,S2|SL],N) :-
        printOut('('), printOut(S1), printOut(','), printOut(N), printOut('), '),
        printPrecedence([S2|SL],N).

readProblem(File,L) :-
%	tmpDir(Dir),
%	generateFilename(Dir,'tancs.in',TMPFILE),
%	systemCall(shell,['grep -v \'\%\' ',File,' | sed -e \'s/\-/\-0/g\' > ',TMPFILE]),
%	systemCall(shell,['cat ',File,' | /users/loco/ullrich/provers/experiments/modal/toprolog > ',TMPFILE]),
	see(File),
	readProblem(L),
	seen,
	!.
readProblem(L) :-
	read(Formula),
	(Formula == end_of_file ->
	    L = []
	;
	    readProblem(L1),
	    !,
	    L = [Formula|L1]
	),
	!.

systemCall(CallPredicate,Command) :-
	systemCall(Command,[],CallPredicate),
	!.
systemCall(_CallPredicate,Command) :-
	write('SystemCall of '),
	write(Command),
	write(' failed.'),
	nl,
	!.

transSyntax([inputformula(_,axiom,I1)|L],[Form1|Ax2],Conj2,PropSym,RelSym) :- !,
	transformSyntax(I1,prop,Form1,PropSym1,RelSym1), !,
	transSyntax(L,Ax2,Conj2,PropSym2,RelSym2), !,
	setUnion(PropSym1,PropSym2,PropSym),
	setUnion(RelSym1,RelSym2,RelSym),
	!.
transSyntax([inputformula(_,hypothesis,I1)|L],Ax2,[not(Form1)|Conj2],PropSym,RelSym) :- !,
	transformSyntax(I1,prop,Form1,PropSym1,RelSym1), !,
	transSyntax(L,Ax2,Conj2,PropSym2,RelSym2), !,
	setUnion(PropSym1,PropSym2,PropSym),
	setUnion(RelSym1,RelSym2,RelSym),
	!.
transSyntax([inputformula(_,conjecture,I1)|L],Ax2,[Form1|Conj2],PropSym,RelSym) :- !,
	transformSyntax(I1,prop,Form1,PropSym1,RelSym1), !,
	transSyntax(L,Ax2,Conj2,PropSym2,RelSym2), !,
	setUnion(PropSym1,PropSym2,PropSym),
	setUnion(RelSym1,RelSym2,RelSym),
	!.
transSyntax([I1|L],Ax2,[Form1|Conj2],PropSym,RelSym) :-
	transformSyntax(I1,prop,Form1,PropSym1,RelSym1), !,
	transSyntax(L,Ax2,Conj2,PropSym2,RelSym2), !,
	setUnion(PropSym1,PropSym2,PropSym),
	setUnion(RelSym1,RelSym2,RelSym),
	!.
transSyntax([],[],[],[],[]) :-
	!.

%transformSyntax(~I1,Type,not(O1),PropSym,RelSym) :-
%	transformSyntax(I1,Type,O1,PropSym,RelSym),
%	!.
transformSyntax(neg I1,Type,not(O1),PropSym,RelSym) :- !,
	transformSyntax(I1,Type,O1,PropSym,RelSym),
	!.
transformSyntax(I1-0,rel,conv(O1),PropSym,RelSym) :- !,
	transformSyntax(I1,rel,O1,PropSym,RelSym),
	!.
transformSyntax(I1|I2,Type,or(O1,O2),PropSym,RelSym) :- !,
	transformSyntax(I1,Type,O1,PropSym1,RelSym1), 
	transformSyntax(I2,Type,O2,PropSym2,RelSym2), 
	setUnion(PropSym1,PropSym2,PropSym),
	setUnion(RelSym1,RelSym2,RelSym),
	!.
transformSyntax(I1\/I2,Type,or(O1,O2),PropSym,RelSym) :- !,
	transformSyntax(I1,Type,O1,PropSym1,RelSym1), 
	transformSyntax(I2,Type,O2,PropSym2,RelSym2), 
	setUnion(PropSym1,PropSym2,PropSym),
	setUnion(RelSym1,RelSym2,RelSym),
	!.
transformSyntax(I1&I2,Type,and(O1,O2),PropSym,RelSym) :- !,
	transformSyntax(I1,Type,O1,PropSym1,RelSym1),
	transformSyntax(I2,Type,O2,PropSym2,RelSym2),
	setUnion(PropSym1,PropSym2,PropSym),
	setUnion(RelSym1,RelSym2,RelSym),
	!.
transformSyntax(I1<=>I2,Type,equiv(O1,O2),PropSym,RelSym) :- !,
	transformSyntax(I1,Type,O1,PropSym1,RelSym1), 
	transformSyntax(I2,Type,O2,PropSym2,RelSym2), 
	setUnion(PropSym1,PropSym2,PropSym),
	setUnion(RelSym1,RelSym2,RelSym),
	!.
transformSyntax(I1=>I2,Type,implies(O1,O2),PropSym,RelSym) :- !,
	transformSyntax(I1,Type,O1,PropSym1,RelSym1), 
	transformSyntax(I2,Type,O2,PropSym2,RelSym2), 
	setUnion(PropSym1,PropSym2,PropSym),
	setUnion(RelSym1,RelSym2,RelSym),
	!.
transformSyntax(I1#I2,_,comp(O1,O2),PropSym,RelSym) :- !,
	transformSyntax(I1,rel,O1,PropSym1,RelSym1),
	transformSyntax(I2,rel,O2,PropSym2,RelSym2),
	setUnion(PropSym1,PropSym2,PropSym),
	setUnion(RelSym1,RelSym2,RelSym),
	!.
transformSyntax(box(I1:box(I1-0:box(I1:I2))),_,box(O1,O2),PropSym,RelSym) :- !,
	transformSyntax(I1,rel,O1,PropSym1,RelSym1),
	transformSyntax(I2,prop,O2,PropSym2,RelSym2),
	setUnion(PropSym1,PropSym2,PropSym),
	setUnion(RelSym1,RelSym2,RelSym),
	!.
transformSyntax(box(I1:I2),_,box(O1,O2),PropSym,RelSym) :- !,
	transformSyntax(I1,rel,O1,PropSym1,RelSym1), 
	transformSyntax(I2,prop,O2,PropSym2,RelSym2),
	setUnion(PropSym1,PropSym2,PropSym),
	setUnion(RelSym1,RelSym2,RelSym),
	!.
transformSyntax(br1(I2),_,box(O1,O2),PropSym,RelSym) :- !,
	transformSyntax(r,rel,O1,PropSym1,RelSym1), 
	transformSyntax(I2,prop,O2,PropSym2,RelSym2),
	setUnion(PropSym1,PropSym2,PropSym),
	setUnion(RelSym1,RelSym2,RelSym),
	!.
transformSyntax(dr1(I2),_,dia(O1,O2),PropSym,RelSym) :- !,
	transformSyntax(r,rel,O1,PropSym1,RelSym1), 
	transformSyntax(I2,prop,O2,PropSym2,RelSym2),
	setUnion(PropSym1,PropSym2,PropSym),
	setUnion(RelSym1,RelSym2,RelSym),
	!.
transformSyntax(I1,Type,I1,PropSym,RelSym) :- !,
	atomic(I1),
	addToSyms(Type,I1,PropSym,RelSym),
	!.

simplList(FL1,FL3) :-
	((simplifyOpt(X), member(purify,X)) ->
		purifyList(FL1,FL2)
	;
	        FL2 = FL1
	),
	F1 =.. [or|FL2],
	!,
	simplify_alc(F1,F2),
	(getArgs(F2,or,FL3) ->
	    true
	;
	    FL3 = [F2]
	).
	
purifyList(FL1,FL3) :-
	levelSymList(FL1,0,FL2,SL1),
	purifySymList(FL2,0,SL1,FL3).

purifySymList([],_,_,[]) :-
	!.
purifySymList([F1|FL1],L1,SymList,[F2|FL2]) :-
	purifySym(F1,L1,SymList,F2),
	purifySymList(FL1,L1,SymList,FL2).

purifySym(box(R1,F1),L1,SymList,box(R1,F2)) :-
	L2 is L1+1,
	purifySym(F1,L2,SymList,F2).
purifySym(dia(R1,F1),L1,SymList,dia(R1,F2)) :-
	L2 is L1+1,
	purifySym(F1,L2,SymList,F2).
purifySym(not(A),L1,SymList,F) :-
	atomic(A),
	(member((A,both,L1),SymList) ->
	    F = not(A)
	;
	    F = false
	),
	!.
purifySym(A,L1,SymList,F) :-
	atomic(A),
	(member((A,both,L1),SymList) ->
	    F = A
	;
	    F = false
	),
	!.
purifySym(F1,L1,SymList,F2) :-
	F1 =.. [Op|FL1],
	purifySymList(FL1,L1,SymList,FL2),
	F2 =.. [Op|FL2].

levelSymList([],_,[],[]) :-
	!.
levelSymList([F1|FL1],L1,[F2|FL2],SymList) :-
	nnf_term(F1,F2),
	levelSym(F2,L1,SymList1),
	levelSymList(FL1,L1,FL2,SymList2),
	mergeSymLists(SymList1,SymList2,SymList).

levelSym(box(_R1,F1),L1,SymList) :-
	L2 is L1+1,
	levelSym(F1,L2,SymList).
levelSym(dia(_R1,F1),L1,SymList) :-
	L2 is L1+1,
	levelSym(F1,L2,SymList).
levelSym(true,_L1,[]) :- 
	!.
levelSym(not(true),_L1,[]) :- 
	!.
levelSym(false,_L1,[]) :- 
	!.
levelSym(not(false),_L1,[]) :- 
	!.
levelSym(not(A),L1,[(A,neg,L1)]) :-
	atomic(A),
	!.
levelSym(A,L1,[(A,pos,L1)]) :-
	atomic(A),
	!.
levelSym(F,L1,SymList) :-
	F =.. [_|FL1],
	levelSymList(FL1,L1,_,SymList).

mergeSymLists([],SL2,SL2) :-
	!.
mergeSymLists([(A,Pol1,Level)|SL1],SL2,SL) :-
	delete(SL2,(A,Pol2,Level),SL3),
	mergeSymLists(SL1,SL3,SL4),
	(var(Pol2) ->
	    SL = [(A,Pol1,Level)|SL4]
	;
	    (Pol1 == Pol2 ->
		SL = [(A,Pol1,Level)|SL4]
	    ;
		SL = [(A,both,Level)|SL4]
	    )
	).

addToSyms(_,true,[],[]) :-
	!.
addToSyms(_,false,[],[]) :-
	!.
addToSyms(prop,I1,[I1],[]) :-
	!.
addToSyms(rel,I1,[],[I1]) :-
	!.


systemCall([],CommandString,shell) :-
	atom_chars(Command,CommandString),
	shell(Command).
systemCall([],CommandString,shell(Result)) :-
	atom_chars(Command,CommandString),
	shell(Command,Result).
systemCall([A|L],CommandString1,CallPredicate) :-
	(number(A) ->
	    number_chars(A,AC)
	;
	    (atomic(A) ->
		atom_chars(A,AC)
	    ;
		AC = A
	    )
	),
	append(CommandString1,AC,CommandString2),
	systemCall(L,CommandString2,CallPredicate).


generateFilename(DIR,Name,File) :-
        atom_chars(DIR,S1),
        atom_chars(Name,S2),
        append(S1,S2,S3),
        atom_chars(File,S3).
