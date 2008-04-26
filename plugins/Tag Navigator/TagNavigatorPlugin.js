/***
|Name|TagNavigatorPlugin (working title)|
|Source|[[FND's DevPad|http://devpad.tiddlyspot.com/#TagNavigatorPlugin]]|
|Version|0.1|
|Author|FND|
|License|[[Creative Commons Attribution-ShareAlike 2.5 License|http://creativecommons.org/licenses/by-sa/2.5/]]|
|~CoreVersion|2.1|
|Type|plugin|
|Requires|N/A|
|Overrides|N/A|
|Description|select tiddlers based on tag combinations|
!Usage
{{{<<TagNav tag1 tag2 tag3 ... >>}}}
''N.B.:'' Tags containing spaces must be enclosed either in {{{[[brackets]]}}} or in {{{"quotation marks"}}}.
!Changelog
!!v0.1 (2007-06-08)
* initial alpha version
!Issues / To Do
* many, many issues; cf. DEBUG markers in code
* popup menu can't be closed yet (can even be opened multiple times!)
* displayed  the count of the number of matching tiddlers
!Code
***/
//{{{
// create TagNavigator namespace
TagNav = {};

// create shadow tiddler for CSS rules
TagNav.addStyles = function(shadowTiddler) {
	config.shadowTiddlers[shadowTiddler] = "/*{{{*/\n"
		+ "#TagNavigator {\n"
		+ "\tposition: absolute;\n" // DEBUG: messy display (overlay issues)
		+ "\tz-index: 99;\n"
		+ "\tmargin: 0;\n"
		+ "\tborder: 1px solid [[ColorPalette::PrimaryDark]];\n"
		+ "\tpadding: 0;\n"
		+ "\tbackground-color: [[ColorPalette::TertiaryPale]];\n"
		+ "}\n\n"
		+ "#TagNavigatorTagBar,\n"
		+ "#TagNavigatorTiddlers {\n"
		+ "\tmargin: 0;\n"
		+ "\tpadding: 5px;\n"
		+ "\tlist-style-type: none;\n"
		+ "}\n\n"
		+ "#TagNavigatorTagBar {\n"
		+ "\tmargin-bottom: 1.2em;\n" // required due to floating
		+ "}\n\n"
		+ "#TagNavigatorTiddlers {\n"
		+ "\tclear: both;\n"
		+ "\tborder-top: 1px solid [[ColorPalette::PrimaryDark]];\n"
		+ "}\n\n"
		+ "#TagNavigatorTagBar li {\n"
		+ "\tfloat: left;\n"
		+ "\tmargin: 0 0.2em 0.5em;\n"
		+ "}\n\n"
		+ "#TagNavigator .buttonClose {\n"
		+ "\tfloat: left;\n" // DEBUG: temporary solution (right-floating expands container width)
		+ "\tmargin: 0 5px 5px 0;\n"
		+ "\tborder: 1px solid [[ColorPalette::PrimaryDark]];\n"
		+ "\tpadding: 0 1px;\n"
		+ "}\n"
		+ "/*}}}*/";
	store.addNotification(shadowTiddler, refreshStyles);
}
TagNav.addStyles("StyleSheetTagNavigator");

/*
** Macro
*/

config.macros.TagNav = {
	label: "Tag Navigator",
	prompt: "select tiddlers based on tag combinations",
	accessKey: null
};

config.macros.TagNav.handler =
	function(place, macroName, params, wikifier, paramString, tiddler) {
		// create macro button
		createTiddlyButton(place, this.label, this.prompt,
			function() {
				// select active button
				TagNav.Btn = this;
				// close (remove) existing popups
				TagNav.popupClose(TagNav.Container, TagNav.active);
				// set activity status				
				TagNav.active = true;
				// process macro parameters (initial tags)
				TagNav.activeTags = [];
				if(params[0]) {
					for(var i = 0; i < params.length; i++) {
						TagNav.activeTags.pushUnique(params[i], true);
					}
				}
				// initialize
				TagNav.initializeTiddlerSelection();
				TagNav.initializeInterface();
				return false; // DEBUG: purpose? obsolete?
			},
			null, null, this.accessKey);
		return false; // DEBUG: purpose? obsolete?
}

/*
** Initialization
*/

// initialize tiddler selection
TagNav.initializeTiddlerSelection = function() {
	// get all tiddlers
	TagNav.tiddlers = store.reverseLookup("tags", "", false, "title"); // DEBUG: dirty hack!?
	// get tiddler titles
	TagNav.titles = TagNav.getTiddlerTitles(TagNav.tiddlers);
	// get tiddler tags
	TagNav.tags = TagNav.getTiddlerTags(TagNav.tiddlers);
	// process initial tags
	if(TagNav.activeTags.length > 0) {
		for(var i = 0; i < TagNav.activeTags.length; i++) {
			// update matching tiddler set
			TagNav.filter(TagNav.activeTags[i])
		}
	} 
}

// initialize Tag Navigator interface
TagNav.initializeInterface = function() {
	// create container elements
	TagNav.Container = createTiddlyElement(document.body, "div", "TagNavigator", null, null); // DEBUG: using document.body as parent only sub-optimal?	 
	btn = createTiddlyButton(TagNav.Container, "x", "close Tag Navigator popup", TagNav.popupClose, "buttonClose", null, null); // close button
	TagNav.TagBar = createTiddlyElement(TagNav.Container, "ul", "TagNavigatorTagBar", null, null);
	TagNav.TiddlerBox = createTiddlyElement(TagNav.Container, "ul", "TagNavigatorTiddlers", null, null);
	// adjust Tag Navigator popup position
	TagNav.popupPosition(TagNav.Btn, TagNav.Container);
	 // add previously-selected filter tags to tag bar
	var item, btn;
	for(var i = 0; i < TagNav.activeTags.length; i++) {
		item = createTiddlyElement(TagNav.TagBar, "li", null, null, null);
		btn = createTiddlyButton(item, TagNav.activeTags[i], null, null, "buttonBland", null, null); // DEBUG: buttonBland class not styled yet
	}
	 // add button for new filter tag to tag bar
	item = createTiddlyElement(TagNav.TagBar, "li", null, null, null);
	btn = createTiddlyButton(item, "+", "add a tag to filter by", TagNav.tagSelection, null, null, null);
	// fill in contents of tiddler
	TagNav.listTiddlerTitles(TagNav.TiddlerBox, TagNav.titles);	
}

/*
** Interface
*/

// event click on tiddler button: open tiddler and close popups
TagNav.tiddlerSelection = function(e) {
	var theTarget = resolveTarget(e);
	// close popup
	TagNav.popupClose();
	// open tiddler
	var title = theTarget.getAttribute("tiddler");
	story.displayTiddler(null, title); // DEBUG: temporary workaround (see below)
	//onClickTiddlerLink(e); // DEBUG: causes error "theLink.getAttribute is not a function"
}

// event click on tag button: add new filtering level
TagNav.tagSelection = function(e) {
	//TagNav.listTiddlerTags(TagNav.TagBar, TagNav.tags); // DEBUG'd: continue here
	/* DEBUG'd
	var theTarget = resolveTarget(e);
	var tag = theTarget.getAttribute("tag");
	if(tag) {
		// update matching tiddler set
		TagNav.filter(tag);
		// create sub-menu -- DEBUG: buggy/incomplete (e.g. how to close popups)
		TagNav.subMenus.push(Popup.create(theTarget));
		var TagNavigator = createTiddlyElement(theTarget, "ul", "TagNavigator", null, null);
		TagNav.listTiddlers(TagNavigator, TagNav.titles);
		TagNav.listTags(TagNavigator, TagNav.tags);
		Popup.show(TagNavigator, false);
	}
	*/
}

// adjust Tag Navigator popup position -- based upon Popup.show()
TagNav.popupPosition = function(parent, popup) {
	var rootLeft = findPosX(parent);
	var rootTop = findPosY(parent);
	var rootHeight = parent.offsetHeight;
	var popupLeft = rootLeft;
	var popupTop = rootTop + rootHeight;
	var popupWidth = popup.offsetWidth;
	var winWidth = findWindowWidth();
	if(popupLeft + popupWidth > winWidth)
		popupLeft = winWidth - popupWidth;
	popup.style.left = popupLeft + "px";
	popup.style.top = popupTop + "px";
	popup.style.display = "block";
	if(anim && config.options.chkAnimate)
		anim.startAnimating(new Scroller(popup, false));
	else
		window.scrollTo(0,ensureVisible(popup));
}

// close (remove) existing popup
TagNav.popupClose = function(popup, check) {
	if(check) {
		popup.parentNode.removeChild(popup);
	}
	check = false;
}

/*
** Tiddler Processing
*/

// retrieve tiddlers with a specific tag from tiddlers array
TagNav.getTaggedTiddlers = function(container, tag) {
	var tiddlers = [];
	for(var i = 0; i < container.length; i++) {
		if(container[i].tags.contains(tag)) {
			tiddlers.push(container[i]);
		}
	}
	return tiddlers;
}

// retrieve titles from tiddlers array
TagNav.getTiddlerTitles = function(container) {
	var titles = [];
	for(var i = 0; i < container.length; i++) {
		titles.push(container[i].title);
	}
	titles.sort();
	return titles;
}

// retrieve tags from tiddlers array
TagNav.getTiddlerTags = function(tiddlers) {
	var tags = [];
	for(var i = 0; i < tiddlers.length; i++) {
		for(var j = 0; j < tiddlers[i].tags.length; j++) {
			tags.pushUnique(tiddlers[i].tags[j]);
		}
	}
	tags.sort();
	return tags;
}

// update matching tiddler set
TagNav.filter = function(tag) {
	TagNav.tiddlers = TagNav.getTaggedTiddlers(TagNav.tiddlers, tag);
	TagNav.titles = TagNav.getTiddlerTitles(TagNav.tiddlers);
	TagNav.tags = TagNav.getTiddlerTags(TagNav.tiddlers);
}

// create list of tiddlers as links
TagNav.listTiddlerTitles = function(parent, titles) {
	var item, btn;
	if(titles.length == 0) {
		createTiddlyElement(parent, "span", null, null, "no matching tiddlers");
	}
	for(var i = 0; i < titles.length; i++) {
		item = createTiddlyElement(parent, "li", null, null, null);
		btn = createTiddlyButton(item, titles[i], "open tiddler", TagNav.tiddlerSelection, "tiddlyLink tiddlyLinkExisting");
		btn.setAttribute("tiddler", titles[i]);
	}
}

// create list of tags as links -- DEBUG: revise titles and prompts
TagNav.listTiddlerTags = function(parent, tags) {
	var item, btn;
	if(tags.length == 0) {
		createTiddlyElement(parent, "li", null, null, "no matching tags");
	}
	for(var i = 0; i < tags.length; i++) {
		item = createTiddlyElement(parent, "li", null, null, null);
		btn = createTiddlyButton(item, tags[i], "filter using this tag", TagNav.tagSelection);
		btn.setAttribute("tag", tags[i]);
	}
}
//}}}