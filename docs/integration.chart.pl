# plugin integraton
# (Graph::Easy flowchart)
#
# To Do:
# * code sanitizing

graph			{ flow: south; }
node.edge		{ shape: rect; fill: #ffbfc9; }
node.action		{ shape: rounded; fill: #8bef91; }
node.decision	{ shape: diamond; fill: #ffff8a; }


[ Start ] { class: edge; }
	--> [ retrieve fields ]

[ retrieve fields ] { class: action; }
	--> [ has tag\n "systemConfig" ]

[ has tag\n "systemConfig" ] { class: decision; }
	-- Y --> [ retrieve slices ]
[ has tag\n "systemConfig" ]
	-- N --> [ Skip ]

[ retrieve slices ] { class: action; }
	--> [ has slice\n "Name" ]

[ has slice\n "Name" ] { class: decision; }
	-- Y --> [ has slice\n "Source" ] 
[ has slice\n "Name" ]
	-- N --> [ set name to\n tiddler title ]

[ set name to\n tiddler title ] { class: action; }
	--> [ has slice\n "Source" ]

[ has slice\n "Source" ] { class: decision; }
	-- Y --> [ source is\n current repository ]
[ has slice\n "Source" ]
	-- N --> [ set source to\n current repository ]

[ source is\n current repository ] { class: decision; }
	-- Y --> [ compose plugin identifier\n (source + name) ]
[ source is\n current repository ]
	-- N --> [ source is\n in repositories ]

[ source is\n in repositories ] { class: decision; }
	-- Y --> [ Skip ]
[ source is\n in repositories ]
	-- N --> [ Report New ]

[ set source to\n current repository ] { class: action; }
	--> [ compose plugin identifier\n (source + name) ]

[ compose plugin identifier\n (source + name) ] { class: action; }
	--> [ plugin is blacklisted ]

[ plugin is blacklisted ] { class: decision; }
	-- Y --> [ Skip ]
[ plugin is blacklisted ]
	-- N --> [ retrieve documentation ]

[ retrieve documentation ] { class: action; }
	--> [ plugin in database ]

[ plugin in database ] { class: action; }
	-- Y --> [ update database ]
[ plugin in database ]
	-- N --> [ add to database ]

[ add to database ] { class: edge; }

[ update database ] { class: edge; }

[ Skip ] { class: edge; }

[ Report New ] { class: edge; }
