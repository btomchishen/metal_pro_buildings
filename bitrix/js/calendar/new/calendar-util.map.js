{"version":3,"sources":["calendar-util.js"],"names":["window","Util","calendar","config","additionalParams","this","userSettings","dayLength","type","userId","parseInt","ownerId","accessNames","handleAccessNames","DATE_FORMAT_BX","BX","message","DATETIME_FORMAT_BX","DATE_FORMAT","date","convertBitrixFormat","DATETIME_FORMAT","substr","length","TIME_FORMAT","util","trim","TIME_FORMAT_BX","isAmPmMode","TIME_FORMAT_SHORT_BX","replace","TIME_FORMAT_SHORT","KEY_CODES","backspace","enter","escape","space","delete","left","right","up","down","z","y","shift","ctrl","alt","cmd","cmdRight","pageUp","pageDown","prototype","getWeekDays","weekDays","getWeekStart","weekStart","getWeekEnd","MO","TU","WE","TH","FR","SA","SU","getWeekDayOffset","day","weekDayOffsetIndex","i","getWeekDayByInd","index","isHoliday","weekHolidays","week_holidays","hasOwnProperty","yearHolidays","year_holidays","yearWorkdays","year_workdays","getDay","monthDate","getDate","month","getMonth","isToday","curDate","Date","getFullYear","getWorkTime","userWorkTime","work_time_start","work_time_end","workTime","start","Math","floor","parseFloat","end","ceil","proxy","setWorkTime","min","max","userOptions","save","formatTime","h","m","skipMinutes","isDate","getMinutes","getHours","res","undefined","isNaN","toString","ampm","formatDate","timestamp","getTime","format","formatDateTime","formatDateUsable","showYear","parseTime","str","parseDate","trimSeconds","cnt","k","regMonths","bUTC","isNotEmptyString","expr","RegExp","aDate","match","aFormat","aDateArgs","aFormatArgs","aResult","array_search","getNumMonth","toUpperCase","d","setUTCDate","setUTCFullYear","setUTCMonth","setUTCHours","setDate","setFullYear","setMonth","setHours","bPM","findTargetNode","node","parentCont","prefix","viewsCont","attributes","name","findParent","n","j","getViewHeight","minHeight","height","GetWindowInnerSize","document","innerHeight","showWeekNumber","getUserOption","getWeekNumber","weekNumber","getScrollbarWidth","browser","IsMac","result","outer","mainCont","appendChild","create","props","className","widthNoScroll","offsetWidth","style","overflow","inner","widthWithScroll","cleanNode","getMessagePlural","messageId","number","Loc","defaultValue","setUserOption","value","getKeyCodes","getMousePos","e","event","x","pageX","pageY","clientX","clientY","documentElement","scrollLeft","body","clientLeft","scrollTop","clientTop","getDayCode","getTextColor","color","charAt","substring","r","g","b","light","getTimeValue","round","getTimeEx","getTimeByInt","intValue","hour","getTimeByFraction","val","useFloor","getWeekNumberInMonthByDate","origDate","getSimpleTimeList","push","label","adaptTimeValue","timeValue","timeList","diff","ind","abs","getMeetingRoomList","meetingRooms","mergeSocnetDestinationConfig","socnetDestination","USERS","code","getSocnetDestinationConfig","key","users","groups","EXTRANET_USER","DENY_TOALL","UA","id","DEPARTMENT","sonetgroups","SONETGROUPS","department","departmentRelation","DEPARTMENT_RELATION","LAST","SELECTED","getActionUrl","actionUrl","getTimezoneList","timezoneList","getDefaultColors","defaultColorsList","getFormSettings","formType","formSettings","saveFormSettings","settings","pinnedFields","randomInt","random","getAccessName","setAccessName","getSectionAccessTasks","sectionAccessTasks","getTypeAccessTasks","typeAccessTasks","getDefaultTypeAccessTask","taskId","accessTasks","getDefaultSectionAccessTask","getSuperposedTrackedUsers","trackingUsersList","sort","a","LAST_NAME","localeCompare","getSuperposedTrackedGroups","trackingGroupList","isUserCalendar","isGroupCalendar","userIsOwner","hexToRgb","hex","exec","hexToRgba","opacity","parseLocation","ar","split","mrid","mrevid","room_id","room_event_id","getTextLocation","location","ID","NAME","locationList","Calendar","Controls","Location","getLocationList","getTextReminder","in_array","getEditTaskPath","editTaskPath","getViewTaskPath","viewTaskPath","readOnlyMode","readOnly","sectionList","sectionController","getSectionListForEdit","getLoader","size","html","isFilterEnabled","counters","getCalDavConnections","connections","isRichLocationEnabled","locationFeatureEnabled","isDarkColor","toLowerCase","#86b100","#0092cc","#00afc7","#da9100","#00b38c","#de2b24","#bd7ac9","#838fa0","#ab7917","#e97090","#9dcf00","#2fc6f6","#56d1e0","#ffa900","#47e4c2","#f87396","#9985dd","#a8adb4","#af7e00","getAvilableViews","avilableViews","getCustumViews","customViews","placementParams","gridPlacementList","isMeetingsEnabled","bSocNet","bIntranet","isAccessibilityEnabled","isPrivateEventsEnabled","useViewSlider","showEventDescriptionInSimplePopup","doBxContextFix","top","Object","keys","forEach","__BX","Access","SocNetLogDestination","restoreBxContextFix","BXEventCalendar","addCustomEvent"],"mappings":"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","file":"calendar-util.map.js"}