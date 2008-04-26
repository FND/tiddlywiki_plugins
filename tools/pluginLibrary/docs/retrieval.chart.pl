# plugin retrieval
# (Graph::Easy flowchart)
#
# To Do:
# * code sanitizing

graph			{ flow: south; }
node.edge		{ shape: rect; fill: #ffbfc9; }
node.action		{ shape: rounded; fill: #8bef91; }
node.decision	{ shape: diamond; fill: #ffff8a; }


[ Start ] { class: edge; }
	--> [ select repository ]

[ select repository ] { class: action; }
	-- * --> [ is TiddlyWiki ]

[ is TiddlyWiki ] { class: decision; }
	-- Y --> [ retrieve tiddlers ]
[ is TiddlyWiki ]
	-- N --> [ is directory ]

[ is directory ] { class: decision; }
	-- Y --> [ retrieve files ]
[ is directory ]
	-- N --> [ is JS file ]

[ retrieve tiddlers ] { class: action; }
	-- * --> [process plugin]

[ retrieve files ] { class: action; }
	-- * --> [ is JS file ]

[ is JS file ] { class: decision; }
	-- Y --> [ retrieve metadata ]

[ retrieve metadata ] { class: action; }
	--> [ compose plugin ]

[ compose plugin ] { class: action; }
	--> [ process plugin ]

[ process plugin ] { class: edge; }
