//Helper function assembling the current query parameters from the input controls
// into a JS object.
function currentQuery(name, place) {
	var nameterm = name || $("#partner").val().trim();
	nameterm = nameterm.replace(/\s*\(\d+\)$/, "");
	var placeterm = place || $("#place").val().trim();
	placeterm = placeterm.replace(/\s*\(\d+\)$/, "");
	return {
		term: nameterm,
		from: $.datepicker.formatDate('yy-mm-dd', $("#date_from").datepicker("getDate")),
		to: $.datepicker.formatDate('yy-mm-dd', $("#date_to").datepicker("getDate")),
		type: $("input[name='letterType']:checked").val(),
		place: placeterm,
		copybook: $("#num_copybook").is(":visible") ? $("#num_copybook").val().trim() : null,
		box: $("#num_box").is(":visible") ? $("#num_box").val().trim() : null,
		withMedia: $("#check_digitized").is(':checked') 
	};
}


//Update the state of the search button
function updateSearch() {
	var query = currentQuery();
	with(query) {
		if((term && term.length)
			|| (place && place.length)
			|| (from && from.length)
			|| (to && to.length)
			|| (copybook && copybook.length && copybook != "0")
			|| (box && box.length && box != "0")) {
			$("#query_items").button("enable");
			count_items();
		}
		else {
			$("#query_items").button("disable");
			count_items();
		}
	}
}

//Update the dates section after query parameters have been changed
function updateSectionDate() {
	if($("#date_from").datepicker("getDate") || $("#date_to").datepicker("getDate")) {
		$("#section-dates").addClass("sectionActive");
	}
	else {
		$("#section-dates").removeClass("sectionActive");
	}
}

//Update the dates section after query parameters have been changed
function updateSectionCorrespondent() {
	if($("#partner").val().trim().length || $("input[name='letterType']:checked").val() != "both") {
		$("#section-correspondent").addClass("sectionActive");
	}
	else {
		$("#section-correspondent").removeClass("sectionActive");
	}
}

//Update the dates section after query parameters have been changed
function updateSectionPlace() {
	if($("#place").val().trim().length) {
		$("#section-place").addClass("sectionActive");
	}
	else {
		$("#section-place").removeClass("sectionActive");
	}
}

//Update the dates section after query parameters have been changed
function updateSectionOptions() {
	if($("#check_digitized").is(':checked')) {
		$("#section-options").addClass("sectionActive");
	}
	else {
		$("#section-options").removeClass("sectionActive");
	}
}


//Ajax call to query_partners.php
//The returned array builds the autocomplete list for the names field
function queryPartners(request, callback) {
	$.ajax("query_partners.php", 
		{
			success: function(data) {
				callback(data);
			},
			error: function(xhr, status, err) {
				callback(["Error"]);
			},
			data: currentQuery(request.term),
			dataType: "json"
		}
	);
}

//Ajax call to query_places.php
//The returned array builds the autocomplete list for the place field
function queryPlaces(request, callback) {
	$.ajax("query_places.php", 
		{
			success: function(data) {
				callback(data);
			},
			error: function(xhr, status, err) {
				callback(["Error"]);
			},
			data: currentQuery(null, request.term),
			dataType: "json"
		}
	);
}

//Ajax call to query_copybook.php
//The returned array builds the autocomplete list for the copybook field
function queryCopybook() {
	$.ajax("query_copybook.php", 
		{
			success: function(data) {
				$("#num_copybook").html(data);
				var cnt = $("#num_copybook option").length - 1;
				$("#label_copybook").text("Copybook" + (cnt > 0 ? (" (" + cnt + "):") : ":"));
			},
			error: function(xhr, status, err) {
				alert(err);
			},
			data: currentQuery(),
			dataType: "html"
		}
	);
}

//Ajax call to query_box.php
//The returned array builds the autocomplete list for the copybook field
function queryBox() {
	$.ajax("query_box.php", 
		{
			success: function(data) {
				$("#num_box").html(data);
				var cnt = $("#num_box option").length - 1;
				$("#label_box").text("Box" + (cnt > 0 ? (" (" + cnt + "):") : ":"));
			},
			error: function(xhr, status, err) {
				alert(err);
			},
			data: currentQuery(),
			dataType: "html"
		}
	);
}

//Count items and update Search button text:
// Ajax call to count_items.php
function count_items() {
	$.ajax("count_items.php", 
	{
		success: function(data) {
			$("#query_items span").text("Display results (" + data + ")");
			$("#query_items").button("enable");
		},
		error: function(xhr, status, err) {
			$("#query_items").$("#query_items span").text("Display results");
			$("#query_items").button("disable");
		},
		data: currentQuery(),
		dataType: "json"
	});
}


//Launch search and display result table:
// Ajax call to query_items.php, set the #results DIV's contents
// to the received data
function query_items() {
	$.ajax("query_items.php", 
	{
		success: function(data) {
			//Set table data
			$("#results").html(data);
			var query = currentQuery();
			with(query) {
				var href = "download_items.php?term=" + term
					+ "&from=" + from
					+ "&to=" + to
					+ "&type=" + type
					+ "&place=" + place
					+ "&copybook=" + (copybook||"")
					+ "&box=" + (box||"")
					+ "&withMedia=" + (withMedia ? "true" : "false");
				$("#download_items").attr("href", href);
			}
			$("#download_items").button("enable");
			//Hook ID links
			$(".show-details").click(function() {
				query_details($(this));
			});
			//Make table sortable
			$("#resultstable").tablesorter({
				cssHeader: "resultsHeader",
				textExtraction: textExtractor
			}); 
			$("#resultstable").bind("sortStart",function() { 
				$(".itemDetails").remove();
			});
		},
		error: function(xhr, status, err) {
			$("#results").html(err);
		},
		data: currentQuery(),
		dataType: "html"
	});
}

//Ajax call to query_details.php
//On success, insert a (merged) table row below the triggering link
// and fill it with the received HTML
function query_details(link) {
	$.ajax("query_details.php", 
	{
		context: link,
		success: function(data) {
			//Expand details
			var id = $(this).attr("id");
			var row = $("<tr class='itemDetails'><td colspan='6'>" + data + "</tr>");
			$(this).closest("tr").after(row);
			//Zoomple
			$(row).find(".thumbnails a").zoomple();
			//Setup link
			$(this).off("click");
			$(this).click(function() {
				$(this).closest("tr").next().remove();
				$(this).off("click");
				$(this).click(function() {
					query_details($(this));
				});
			});
		},
		error: function(xhr, status, err) {
			alert(err);
		},
		data: {
			id: $(link).attr("id")
		},
		dataType: "html"
	});
}

//Show a given help panel, hiding all others
function showHelp(id) {
	$(".help").hide();
	$("#" + id).fadeIn();
}

//Hide all help
function hideHelp(id) {
	if(id) {
		$("#" + id).fadeOut();
	}
	else {
		$(".help").hide();
	}
}

//Extract table cell contents for sorting: check for sortKey span
function textExtractor(node) {
	var key = $(node).find(".sortKey");
	return key.length > 0 ? key.text() : $(node).text();
}

//Render autocomplete item with count column
function renderItemWithCount(ul, item) {
	return $("<li>")
		.append("<div class=\"item_label\"><a>" + item.label + "</a></div><div class=\"item_count\"><span>" + item.count + "</span></div>")
		.appendTo(ul);
};

//Convert date slider value to Date
var SLIDER_MAX = ((1891 - 1840) * 12) - 3;
function convertSliderValue(val) {
	var totalMonths = val + 2; //Months since 01/1841
	var offsetYear = Math.floor(totalMonths / 12);  //Years since 1841
	var offsetMonth = (totalMonths % 12) + 1; //Month in year
	return new Date(1841 + offsetYear, offsetMonth - 1, val == SLIDER_MAX ? 31 : 1);
}
function convertSliderDate(date) {
	var year = date.getFullYear();
	var month = date.getMonth();
	var totalMonths = ((year - 1841) * 12 + month) - 2;
	return totalMonths;
}
	


//Setup the page's input elements
$(function() {
	//Global ajax settings:
	//Show/hide activity indicator
	var activeAjaxRequests = 0;
	$.ajaxSetup({
		beforeSend:function(){
			$("#loading").css("visibility", "visible");
			activeAjaxRequests++;
		},
		complete:function(){
			if(0 >= --activeAjaxRequests) {
				$("#loading").css("visibility", "hidden");
				activeAjaxRequests = 0;
			}
		}
	});
	
	//Reset buttons
	$(".button-reset")
	.button({
		icons: {
			primary: "ui-icon-close"
		},
		text: false
	});

	$("#reset-dates").click(function() {
		$("#date_from").val("");
		$("#date_to").val("");
		$("#date_to").datepicker("option", "minDate", "30 March 1841");
		$("#date_from").datepicker("option", "maxDate", "10 December 1891");
		$("#slider_dates").slider("values", 0, 0);
		$("#slider_dates").slider("values", 1, SLIDER_MAX);
		updateSectionDate();
		updateSearch();
		queryCopybook();
		queryBox();
	});

	$("#reset-correspondent").click(function() {
		$("#partner").val("");
		$("#partner").focus();
		$("#letterType_both").click();
		updateSectionCorrespondent();
		updateSearch();
	});

	$("#reset-place").click(function() {
		$("#place").val("");
		$("#place").focus();
		updateSectionPlace();
		updateSearch();
	});

	$("#reset-options").click(function() {
		$("#check_digitized").prop("checked", false);
		updateSectionOptions();
		updateSearch();
	});

	//The correspondent's name:
	//autocompletion
	$("#partner")
	.change(function() {
		updateSectionCorrespondent();
	})
	.autocomplete({
		source: queryPartners,
		delay: 300,
		close: function(event, ui) {
			updateSectionCorrespondent();
			updateSearch();
			queryCopybook();
			queryBox();
		}
	})
	.data("ui-autocomplete")._renderItem = renderItemWithCount; 

	//Show/hide help
	$("#partner").focus(function(e) {
		showHelp("help-correspondant");
	});
	$("#partner").blur(function(e) {
		hideHelp();
		updateSectionCorrespondent();
		updateSearch();
		queryCopybook();
		queryBox();
	});
	//Button
	$("#all_correspondents")
		.button({
			icons: {
				primary: "ui-icon-triangle-1-s"
			},
			text: false
		})
		.click(function() {
			$("#partner").autocomplete("search", "*");
			$("#partner").focus();
		});
	$("#reset_correspondents")
		.button({
			icons: {
				primary: "ui-icon-close"
			},
			text: false
		})
		.click(function() {
			$("#partner").val("");
			$("#partner").focus();
			updateSectionCorrespondent();
			updateSearch();
		});

	//The place:
	//autocompletion
	$("#place")
	.change(function() {
		updateSectionPlace();
	})
	.autocomplete({
		source: queryPlaces,
		delay: 300,
		close: function(event, ui) {
			updateSectionPlace();
			updateSearch();
			queryCopybook();
			queryBox();
		}
	})
	.data("ui-autocomplete")._renderItem = renderItemWithCount;
	//Show/hide help
	$("#place").focus(function(e) {
		showHelp("help-place");
	});
	$("#place").blur(function(e) {
		hideHelp();
		updateSectionPlace();
		updateSearch();
		queryCopybook();
		queryBox();
	});
	//Buttons
	$("#all_places")
	.button({
		icons: {
			primary: "ui-icon-triangle-1-s"
		},
		text: false
	})
	.click(function() {
		$("#place").autocomplete("search", "*");
		$("#place").focus();
	});
	$("#reset_places")
		.button({
			icons: {
				primary: "ui-icon-close"
			},
			text: false
		})
		.click(function() {
			$("#place").val("");
			$("#place").focus();
			updateSectionPlace();
			updateSearch();
		});

	
	//Dates
	var DATE_FMT = "dd MM yy";

	//The starting date:
	//Configure datepicker
	$("#date_from").datepicker({
		inline: false,
		dateFormat: DATE_FMT,
		defaultDate: "30 March 1841",
		changeMonth: true,
		changeYear: true,
		yearRange: "1841:1891",
		minDate: "30 March 1841",
		maxDate: "10 December 1891",
		showOtherMonths: true,
		selectOtherMonths: true,
		onSelect: function(val, inst) {
			//Eventually, adjust end date
			var to = $("#date_to").datepicker("getDate");
			var from = $.datepicker.parseDate(DATE_FMT, val);
			if(from) {
				if(to && to < from) {
					$("#date_to").datepicker("setDate", from);
				}
				$("#date_to").datepicker("option", "minDate", from);
			}
			else {
				$("#date_to").datepicker("option", "minDate", "30 March 1841");
			}
			to = $("#date_to").datepicker("getDate");
			$("#slider_dates").slider("values", 0, convertSliderDate(from));
			if(to) {
				$("#slider_dates").slider("values", 1, convertSliderDate(to));
			}
			updateSectionDate();
			updateSearch();
			queryCopybook();
			queryBox();
		},
		onClose: function() {
			updateSectionDate();
			updateSearch();
		}

	});
	//Buttons
	$("#show_from")
	.button({
		icons: {
			primary: "ui-icon-triangle-1-s"
		},
		text: false
	})
	.click(function() {
		$("#date_from").datepicker("show");
	});
	
	$("#copy_from")
	.button({
		icons: {
			primary: "ui-icon-triangle-1-e"
		},
		text: false
	})
	.click(function() {
		var from = $("#date_from").datepicker("getDate");
		if(from) {
			$("#date_to").datepicker("setDate", from);
			$("#slider_dates").slider("values", 1, convertSliderDate(from));
		}
		updateSectionDate();
		updateSearch();
		queryCopybook();
		queryBox();
	});
	
	$("#reset_from")
	.button({
		icons: {
			primary: "ui-icon-close"
		},
		text: false
	})
	.click(function() {
		$("#date_from").val("");
		$("#slider_dates").slider("values", 0, 0);
		updateSectionDate();
		updateSearch();
		queryCopybook();
		queryBox();
	});

	
	//The end date:
	//Configure datepicker
	$("#date_to").datepicker({
		inline: false,
		dateFormat: DATE_FMT,
		defaultDate: "10 December 1891",
		changeYear: true,
		changeMonth: true,
		yearRange: "1841:1891",
		minDate: "30 March 1841",
		maxDate: "10 December 1891",
		showOtherMonths: true,
		selectOtherMonths: true,
		onSelect: function(val, inst) {
			var from = $("#date_from").datepicker("getDate");
			var to = $.datepicker.parseDate(DATE_FMT, val);
			if(to) {
				if(from && from > to) {
					$("#date_from").datepicker("setDate", val);
				}
				$("#date_from").datepicker("option", "maxDate", to);
			}
			else {
				$("#date_from").datepicker("option", "maxDate", "10 December 1891");
			}
			from = $("#date_from").datepicker("getDate");
			if(from) {
				$("#slider_dates").slider("values", 0, convertSliderDate(from));
			}
			$("#slider_dates").slider("values", 1, convertSliderDate(to));
			updateSectionDate();
			updateSearch();
			queryCopybook();
			queryBox();
		},
		onClose: function() {
			updateSectionDate();
			updateSearch();
		}
	});

	//Buttons
	$("#show_to")
	.button({
		icons: {
			primary: "ui-icon-triangle-1-s"
		},
		text: false
	})
	.click(function() {
		$("#date_to").datepicker("show");
	});
	
	$("#copy_to")
	.button({
		icons: {
			primary: "ui-icon-triangle-1-w"
		},
		text: false
	})
	.click(function() {
		var to = $("#date_to").datepicker("getDate");
		if(to) {
			$("#date_from").datepicker("setDate", to);
			$("#slider_dates").slider("values", 0, convertSliderDate(to));
		}
		updateSectionDate();
		updateSearch();
		queryCopybook();
		queryBox();
	});
	
	$("#reset_to")
	.button({
		icons: {
			primary: "ui-icon-close"
		},
		text: false
	})
	.click(function() {
		$("#date_to").val("");
		$("#slider_dates").slider("values", 1, SLIDER_MAX);
		updateSectionDate();
		updateSearch();
		queryCopybook();
		queryBox();
	});


	$(".dates :text")
		.change(function() {
			from = $("#date_from").datepicker("getDate");
			to = $("#date_to").datepicker("getDate");
			$("#slider_dates").slider("values", 0, convertSliderDate(from));
			$("#slider_dates").slider("values", 1, convertSliderDate(to));
			updateSectionDate();
		})
		.focus(function(e) {
			showHelp("help-dates");
		})
		.blur(function(e) {
			hideHelp();
			updateSearch();
			queryCopybook();
			queryBox();
		});
	//Date slider (one tick = one month)
	$("#slider_dates" ).slider({
		range: true,
		min: 0,
		max: SLIDER_MAX,
		values: [0, SLIDER_MAX],
		slide: function(event, ui) {
			var from = convertSliderValue(ui.values[0]);
			var to = convertSliderValue(ui.values[1]);
			$("#date_from").datepicker("option", "maxDate", to);
			$("#date_to").datepicker("option", "minDate", from);
			$("#date_from").datepicker("setDate", from);
			$("#date_to").datepicker("setDate", to);
		},
		stop: function(event, ui) {
			var from = convertSliderValue(ui.values[0]);
			var to = convertSliderValue(ui.values[1]);
			$("#date_from").datepicker("option", "maxDate", to);
			$("#date_to").datepicker("option", "minDate", from);
			$("#date_from").datepicker("setDate", from);
			$("#date_to").datepicker("setDate", to);
			updateSectionDate();
			updateSearch();
			queryCopybook();
			queryBox();
		}
	});

	
	//Buttonize inout radio buttons
	$("#inout_buttons").buttonset();
	
	//React to inout radio buttons
	$(".inout :radio").change(function() {
		var checked = $("input[name='letterType']:checked").val();
		switch(checked) {
			case "in":
				queryBox();
				$(".location").fadeIn();
				$("#box").fadeIn();
				$("#copybook select").val("0");
				$("#copybook").hide();
				break;
			case "out":
				queryCopybook();
				$(".location").fadeIn();
				$("#box").hide();
				$("#box select").val("0");
				$("#copybook").fadeIn();
				break;
			default:
				$("#box").fadeOut();
				$("#box select").val("0");
				$("#copybook").fadeOut();
				$("#copybook select").val("0");
				$(".location").fadeOut();
		}
		updateSectionCorrespondent();
		updateSearch();
	});
	
	//Fill copybook combo
	queryCopybook();
	$("#num_copybook").change(function() {
		updateSearch();
	});

	//Fill box combo
	queryBox();
	$("#num_box").change(function() {
		updateSearch();
	});
	
	//Buttonize options
	$("#section-options").buttonset();
	$("#check_digitized").click(function() {
		updateSectionOptions();
		updateSearch();
		queryCopybook();
		queryBox();
	});
	$("#label_digitized").hover(
		function() {
			showHelp("help-media");
		},
		function() {
			hideHelp("help-media");
		}
	);

	//Launch search and display result table:
	// Ajax call to query_items.php, set the #results DIV's contents
	// to the received data
	$("#query_items").button().click(query_items);
	$("#query_items").button("disable");
	
	//Buttonize download link
	$("#download_items").button();
	$("#download_items").button("disable");
	
	//Buttons in help texts
	$(".button_down").button({
		icons: {
			primary: "ui-icon-triangle-1-s"
		},
		text: false
	});
	$(".button_left").button({
		icons: {
			primary: "ui-icon-triangle-1-w"
		},
		text: false
	});
	$(".button_right").button({
		icons: {
			primary: "ui-icon-triangle-1-e"
		},
		text: false
	});
	$(".button_reset").button({
		icons: {
			primary: "ui-icon-close"
		},
		text: false
	});

});
