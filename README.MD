Azera Fry
=========
Azera Fry is a Template Engine for PHP created by Masoud Zohrabi. syntax influenced from `Razor` and `Jinja`.
`I was created initial version as University Project.`

## Example
```razor
<title>@block title { Default page title }</title>
```
```razor
<title>@block title { Hi, @user }</title>
<h1>@renderBlock(“title”,{ user: “Masoud” })</h1>
```
```razor
<h1>Hello @User.Name</h1>
```
```razor
@if app.auth.isLogged() {
<span>Hello @app.auth.user.fullName</span>
} else {
<span><a href=”/login”>Login</a></span>
}
```
```razor
<ul>
@for letter in [ “A” , “B” , “C” , “D” ] {
<li>@loop.index - @letter|lower</li>
}
</ul>
```
```razor
@set list {
            <ul>
                <li>One</li>
                <li>Two</li>
            </ul>
        }
@list|grep(“One|Two”)|join(“,”)
```
```razor
@set Car = { Color: “White”, Model: “Peugeot 206” }
<span>My Car is @Car.Model</span>
```

## EBNF
```ebnf
StartCodeTag    ::= “@”
Code            ::= StartCodeTag ( Section | Print | PrintClosure }
Digit           ::= "1"|...|"9"|"0"
Letter          ::= "a"|...|"Z"
Word            ::= { Letter }*
Number          ::= ["+"|"-"] { Digit }*
String          ::= [ '"' | "'" ] { Any Character }* [ '"' | "'" ]
SimpleName      ::= [ "_" | Letter ]
Name            ::= SimpleName { [ "_" | Letter | Digit ] }*
Keyword         ::= “in”
Argument        ::= SimpleName [ "=" Expression ]
Arguments       ::= [ Argument { "," Argument }* ]
Parameter       ::= SimpleName "=" Expression | Expression
Parameters      ::= [ Parameter { "," Parameter }* ]
SectionType     ::= “if” | ”elseif” | ”endif” | ”else” | ”block” | ”set” | ”for”
Section         ::= SectionType [Expression] ( “{“ [Any] “}” | NL | NL [Any] Section )
Print           ::= Name [ “(“ Parameters “)” ] { Filter }*
PrintClosure    ::= “(“ Expression “)”
Call            ::= Name “(“ Parameters “)”
Operator        ::= “-”|”+”|”++”|”<=”|”>=”|”==”|”equals”|”less than”|".."|”more than”|”is”|”not”|”match”|”has”
Filter          ::= “|” Word [ “(“ Parameters “)” ]
Expression      ::= String | Number | Array | Name | Call | Operator | Filter | Hash | Array
EndBlock        ::= StartCodeTag “endblock”
Block           ::= ( “block” SimpleName “{“ [Any] “}” ) | ( “block” SimpleName NL [Any] EndBlock )
For             ::= “for” SimpleName [ "," SimpleName ] "in" Expression “{“ [Any] “}”
Macro           ::= "macro" SimpleName "(" Arguments ")" "{" [Any] "}"
Set             ::= "set" Name ( " = " Expression | "{" [Any] "}" )
HashItem        ::= SimpleName ":" Expression
Hash            ::= "{" [ HashItem { "," HashItem }* ] "}"
Array           ::= "[" [ Expression { "," Expression }* ] "]"
Comment         ::= "/*" [Any] "*/"
```
