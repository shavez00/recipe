var activeRequest = null;
var cache = new Object;
 
$(document).ready(function(){
  // Add theme to appropriate items
  // All widgets
  $(".phpc-event-list a, .phpc-message, .phpc-date, .phpc-bar, .phpc-title, #phpc-summary-view, .phpc-logged, .php-calendar td, .phpc-message, .phpc-dropdown-list ul").addClass("ui-widget");
  // Buttons
  $(".phpc-add").button({
      text: false,
      icons: { primary: "ui-icon-plus" }
    });
  $(".php-calendar input[type=submit], .php-calendar tfoot a, .phpc-button").button();
  // The buttons are too hard to read waiting on:
  //    http://wiki.jqueryui.com/w/page/12137730/Checkbox
  // $(".php-calendar input[type=checkbox] + label").prev().button();
  $(".phpc-date, .phpc-event-list a, .phpc-calendar th.ui-state-default").on('mouseover mouseout',
      function (event) {
        $(this).toggleClass("ui-state-hover");
      });
  $(".phpc-date .phpc-add").on('mouseover mouseout',
      function (event) {
        $(this).parent(".phpc-date").toggleClass("ui-state-hover");
      });
  $(".phpc-date").click(function () {
        window.location.href = $(this).children('a').attr('href');
      });
  // fancy corners
  $(".phpc-event-list a, .phpc-message, .phpc-bar, .phpc-title, #phpc-summary-view, .phpc-logged, .phpc-dropdown-list ul").addClass("ui-corner-all");
  // add jquery ui style classes
  $(".php-calendar td, #phpc-summary-view, .phpc-dropdown-list ul").addClass("ui-widget-content");
  $(".phpc-event-list a, .phpc-message").addClass("ui-state-default");

  // Find the color of ui-widget-content
  var $tempElem = $("<p class=ui-widget-content></p>").hide().appendTo("body");
  var color = $tempElem.css("color");
  $(".phpc-dropdown-list ul a").css("color", color);
  $tempElem.remove();

  // Tabs - Persistence reference: http://stackoverflow.com/questions/19539547/maintaining-jquery-ui-previous-active-tab-before-reload-on-page-reload
  var currentTabId = "0";
  $tab = $(".phpc-tabs").tabs({
      activate: function (e, ui) {
          currentTabId = ui.newPanel.attr("id");
          sessionStorage.setItem("phpc-tab-index", currentTabId);
      }
  });
  var haveTabs = false;
  $(".phpc-tabs").each (function () {
    haveTabs = true;
    if (sessionStorage.getItem("phpc-tab-index") != null) {
      currentTabId = sessionStorage.getItem("phpc-tab-index");
      var index = $(this).find('a[href="#' + currentTabId + '"]').parent().index();
      if (index > 0)
        $tab.tabs('option', 'active', index);
    }
  });
  if (!haveTabs) {
    sessionStorage.removeItem("phpc-tab-index");
  }

  // Summary init
  $("#phpc-summary-view").hide();
  $(".phpc-event-list li").mouseenter(function() {
    showSummary(this, $(this).find("a").attr("href"));
  }).mouseleave(function() {
    hideSummary();
  });

  // Multi select stuff
  var select_id = 1;
  var options = new Array();
  var default_option = false;
  $(".phpc-multi-select").each(function() {
    var master_id = "phpc-multi-master"+select_id
    $(this).before("<select class=\"phpc-multi-master\" id=\""+master_id+"\"></select>");
    $(this).children().each(function() {
      if($(this).prop("tagName") == "OPTION") {
        var val = $(this).attr("value");
        $("#"+master_id).append("<option value=\""+val+"\">"+$(this).text()+"</option>");
        options[val] = [this];
        if($(this).attr("selected") == "selected")
          default_option = val;
      } else if($(this).prop("tagName") == "OPTGROUP") {
        var val = $(this).attr("label");
        var sub_options = new Array();
        $("#"+master_id).append("<option value=\""+val+"\">"+val+"</option>");
        $(this).children().each(function() {
          sub_options.push(this);
          if($(this).attr("selected") == "selected")
            default_option = val;
        });
        options[val] = sub_options;
      }
    });
    if(default_option !== false)
      $("#"+master_id).val(default_option);
    var select = this;
    $("#"+master_id).each(function() {
      var val = $("#"+master_id+" option:selected").attr("value");
      $(select).empty();
      for(var key in options[val]) {
        $(select).append(options[val][key]);
      }
    });
    $("#"+master_id).change(function() {
      var val = $("#"+master_id+" option:selected").attr("value");
      $(select).empty();
      for(var key in options[val]) {
        $(select).append(options[val][key]);
      }
    });
    select_id++;
  });

  // Generic form stuff
  $(".form-select").each(function(){
    formSelectUpdate($(this));
  });
  $(".form-select").change(function(){
    formSelectUpdate($(this));
  });
  $(".form-color-input").after(function() {
    var picker = $("<div>").farbtastic($(this));
    var container = $("<div class=\"phpc-color-wheel ui-widget-content\">").append(picker).hide();
    $(this).click(function() {
        $(document).click(function(e) {
          var p = container.parent();
          if (!p.is(e.target) && p.has(e.target).length == 0) {
            container.hide();
          }
        });
        container.show();
      });
    return container;
  });

  // Dropdown list stuff
  $(".phpc-dropdown-list").each(function(index, elem) {
    var titleElement = $(elem).children(".phpc-dropdown-list-header");
    var listElement = $(elem).children("ul");
    $(document).mouseup(function(e) {
      var container = $(elem);

      if (!container.is(e.target) // if the target of the click isn't the container...
        && container.has(e.target).length === 0) // ... nor a descendant of the container
      {
        listElement.hide();
      }
    });
    var positionList = function() {
        listElement.css("left", titleElement.offset().left);
        listElement.css("top", titleElement.offset().top +
		titleElement.outerHeight());
        listElement.css("min-width", titleElement.outerWidth());
    }
    var button = $("<a>")
      .appendTo(titleElement)
      .addClass("phpc-icon-link fa fa-caret-down")
      .click(function() {
        $(window).resize(positionList);
        positionList();
        listElement.toggle();
      });
      $(this).find(".phpc-dropdown-list-title").click(function() {
        $(window).resize(positionList);
        positionList();
        listElement.toggle();
      });

    listElement.hide();
  });

  // Confirmation dialog stuff
  $("[id^='phpc-dialog']").dialog({
    autoOpen: false,
    modal: true
  });
  // Add this class to links that should open the dialog to confirm
  $("[class*='phpc-confirm']").click(function(e) {
    e.preventDefault();
    var href = $(this).attr("href");
    var re = /phpc-confirm(\S*)/;
    var myArray = re.exec($(this).attr('class'));
    var dialog = "#phpc-dialog" + myArray[1];
    $(dialog).dialog('option', 'buttons', {
      "OK": function() {
        window.location.href = href;
      },
      Cancel: function() {
        $(this).dialog("close");
      }
    })
    .dialog("open");
  });
  // Add this class to forms that should be confirmed
  $(".phpc-form-confirm").submit(function(e) {
    e.preventDefault();
    var form = this;
    $("#phpc-dialog").dialog('option', 'buttons', {
      "OK": function() {
        $(form).off("submit");
        $(form).submit();
      },
      Cancel: function() {
        $(this).dialog("close");
      }
    })
    .dialog("open");
  });

  // Calendar specific/hacky stuff
  if($("#phpc-modify").length > 0 && !$("#phpc-modify").prop("checked"))
    toggle_when(false);

  $("#phpc-modify").click(function() {
      toggle_when($(this).prop("checked"));
    });

  $("#time-type").change(function(){
    if($(this).val() == "normal") {
      $("#start-time").show();
      $("#end-time").show();
    } else {
      $("#start-time").hide();
      $("#end-time").hide();
    }
  });

  $("#time-type").each(function(){
    if($(this).val() == "normal") {
      $("#start-time").show();
      $("#end-time").show();
    } else {
      $("#start-time").hide();
      $("#end-time").hide();
    }
  });

  var dateRelation = compareDates("start", "end");
  $("#start-date select").change(function(){
    if(dateRelation == 0) {
      copyDate("start", "end");
    } else {
      dateRelation = compareDates("start", "end");
      /*if(dateRelation > 0) {
        copyDate("start", "end");
	dateRelation = 0;
      }*/
    }
  });
  $("#end-date select").change(function(){
    dateRelation = compareDates("start", "end");
    /*if(dateRelation > 0) {
      copyDate("end", "start");
      dateRelation = 0;
    }*/
  });

});

function toggle_when(on) {
  $('.phpc-when input[name!="phpc-modify"], .phpc-when select').prop('disabled', !on);
}

function formSelectUpdate(select) {
  var idPrefix = "#" + select.attr("name") + "-";
  select.children("option:not(:selected)").each(function(){
    $(idPrefix + $(this).val()).hide();
  });
  select.children("option:selected").each(function(){
    $(idPrefix + $(this).val()).show();
  });
}

// return 0 on equal 1 on prefix1 > prefix, -1 on prefix1 < prefix2
function compareDates(prefix1, prefix2) {
  var year1 = parseInt($("#" + prefix1 + "-year").val());
  var year2 = parseInt($("#" + prefix2 + "-year").val());
  if(year1 > year2)
    return 1;
  if(year1 < year2)
    return -1;

  var month1 = parseInt($("#" + prefix1 + "-month").val());
  var month2 = parseInt($("#" + prefix2 + "-month").val());
  if(month1 > month2)
    return 1;
  if(month1 < month2)
    return -1;

  var day1 = parseInt($("#" + prefix1 + "-day").val());
  var day2 = parseInt($("#" + prefix2 + "-day").val());
  if(day1 > day2)
    return 1;
  if(day1 < day2)
    return -1;

  return 0;
}

function copyDate(date1, date2) {
  $("#" + date2 + "-year").val($("#" + date1 + "-year").val());
  $("#" + date2 + "-month").val($("#" + date1 + "-month").val());
  $("#" + date2 + "-day").val($("#" + date1 + "-day").val());
}

// sets he specified text in the floating div
function setSummaryText(title,author,time,description,category) {
	$("#phpc-summary-title").html(title);
	$("#phpc-summary-author").html(author);
	$("#phpc-summary-time").html(time);
	$("#phpc-summary-body").html(description);
	$("#phpc-summary-category").html(category);
}
 
// set the location of the div relative to the current link and display it
function showSummaryDiv(elem) {
	
	var div = $("#phpc-summary-view");
	var newTop = $(elem).offset().top + $(elem).innerHeight();

	$(elem).append(div);

	if(newTop + div.outerHeight()
			> $(window).height() + $(window).scrollTop())
		newTop -= $(elem).outerHeight() + div.outerHeight();
	
	var newLeft = $(elem).offset().left - ((div.outerWidth()
			- $(elem).outerWidth()) / 2)
	if(newLeft < 1)
		newLeft = 1;
	else if(newLeft + div.outerWidth() > $(window).width()) 
		newLeft -= (newLeft + div.outerWidth()) - $(window).width();

	div.css("top", newTop + "px");
	div.css("left", newLeft + "px");
	div.show();
}
 
// shows the summary for a particular anchor's url. This will display cached data after the first request
function showSummary(elem, href) {
	if( cache[href] != null ) {
		var data = cache[href];
		setSummaryText(data.title,data.author,data.time,data.body,
				data.category);
		showSummaryDiv(elem);
	}
	else {
		// abort any pending requests
		if( activeRequest != null )
			activeRequest.abort();
		
		// get the calendar data
		activeRequest = $.getJSON(href + "&content=json",
			function(data) {
				cache[href] = data;
				setSummaryText(data.title,data.author,data.time,
					data.body,data.category);
				showSummaryDiv(elem);
				activeRequest = null;
			});
	}	
}
 
// hides the event summary information
function hideSummary() {
	// abort any pending requests
	if( activeRequest != null )
		activeRequest.abort();

	$("#phpc-summary-view").hide();
	setSummaryText('','','','','');
}
