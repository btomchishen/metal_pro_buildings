{"version":3,"sources":["calendar-view.js"],"names":["window","View","calendar","this","util","entryController","name","title","enabled","contClassName","isBuilt","animateClass","collapseOffHours","getUserOption","hotkey","entries","entriesIndex","BX","addCustomEvent","proxy","handleClick","prototype","build","viewCont","create","props","className","show","style","display","setTitle","redraw","displayEntries","hide","getName","getContainer","viewTitle","innerHTML","replace","getIsBuilt","fadeAnimation","container","duration","callback","easing","start","opacity","finish","transition","makeEaseOut","transitions","quad","step","state","complete","type","isFunction","animate","showAnimation","removeAttribute","getArrow","color","fill","borderColor","urlencode","fillColor","imageSource","arrowNode","backgroundImage","occupySlot","params","days","i","startIndex","endIndex","slots","slotIndex","showCompactEditForm","isExternalMode","triggerEvent","setTimeout","delegate","closeCallback","Calendar","EntryManager","openCompactEditForm","isLocationCalendar","locationAccess","config","ownerId","userId","sections","roomsManager","getSections","trackingUserList","getSuperposedTrackedUsers","entryTime","userSettings","locationFeatureEnabled","isRichLocationEnabled","locationList","Controls","Location","getLocationList","iblockMeetingRoomList","getMeetingRoomList","plannerFeatureEnabled","sectionManager","showCompactViewForm","openCompactViewForm","entry","calendarContext","Util","getCalendarContext","showEditSlider","simpleViewPopup","close","openEditSlider","parseInt","currentUser","id","handleEntryClick","getEntryById","uid","isTask","SidePanel","Instance","open","getViewTaskPath","loader","showViewSlider","useViewSlider","openViewSlider","from","timezoneOffset","data","TZ_OFFSET_FROM","isActive","currentViewName","uniqueId","undefined","selectEntryPart","wrapNode","backupWrapNodeClass","addClass","blockBackgroundNode","backupBlockOpacity","innerContainer","backupBackground","background","backupBorderColor","backgroundColor","nameNode","backupNameColor","timeNode","backupTimeColor","backupTimeZIndex","zIndex","deselectEntry","selectedEntry","deselect","parts","forEach","part","remove","additionalInfoOuter","getSelectedEntry","preloadEntries","showAllEventsInPopup","entrieList","day","list","innerCont","popup","events","click","handleViewsClick","sort","taskWrap","eventsWrap","entryItem","appendChild","text","message","displayEntryPiece","holder","popupMode","PopupWindowManager","hiddenStorage","autoHide","closeByEsc","offsetTop","offsetLeft","getDayWidth","lightShadow","content","setAngle","offset","allEventsPopup","destroy","showNavigationCalendar","rightBlock","navCalendar","BXEventCalendar","NavigationCalendar","wrap","initialViewShow","mainCont","result","node","offsetWidth","Math","min","getAdjustedDate","date","viewRange","Date","getTime","end","viewRangeDate","setHours","getViewRange","getViewRangeDate","endDate","getHotkey","YearView","apply","arguments","Object","constructor","CalendarView","CalendarYearView","BXEventCalendarView"],"mappings":"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","file":"calendar-view.map.js"}