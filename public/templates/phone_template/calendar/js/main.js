(function($) {

	"use strict";

	document.addEventListener('DOMContentLoaded', function(){
    var today = new Date(),
        year = today.getFullYear(),
        month = today.getMonth(),
        monthTag =["មករា-January","កុម្ភៈ-February","មីនា-March","មេសា-April","ឧសភា-May","មិថុនា-June","កក្កដា-July","សីហា-August","កញ្ញា-September","តុលា-October","វិច្ឋិកា-November","ធ្នូ-December"],
        day = today.getDate(),
        days = document.getElementsByTagName('td'),
        selectedDay,
        setDate,
        holidayList,
        daysLen = days.length;
// options should like '2014-01-01'
    function Calendar(selector, options) {
        this.options = options;
        this.draw();
    }
    
    Calendar.prototype.draw  = function() {
        this.getCookie('selected_day');
        this.getOptions();
        this.drawDays();
        var that = this,
            reset = document.getElementById('reset'),
            pre = document.getElementsByClassName('pre-button'),
            next = document.getElementsByClassName('next-button');
            
            pre[0].addEventListener('click', function(){
            	//alert('pre month');
            	that.preMonth(); });
            next[0].addEventListener('click', function(){
            	//alert('next month');
            	that.nextMonth();
            });
            
            reset.addEventListener('click', function(){that.reset(); });
        while(daysLen--) {
            days[daysLen].addEventListener('click', function(){that.clickDay(this); });
        }
    };
    
    
    Calendar.prototype.drawHeader = function(e) {
    	//alert('click5');
    	holidayList=20;
        var headDay = document.getElementsByClassName('head-day'),
            headMonth = document.getElementsByClassName('head-month');

            e?headDay[0].innerHTML = e : headDay[0].innerHTML = day;
            headMonth[0].innerHTML = monthTag[month] +" - " + year;   
            var currentMonth =year+'-'+(parseInt(month)+1);
            getAllHoliday(currentMonth);
            //this.getAllHolidays(currentMonth);
            //alert('after'+holidayList);
           // this.drawDays();
     };
     Calendar.prototype.getAllHolidays = function() {
    	 //alert('inner function ');
    	 var url = 'http://localhost/camappgit/studentapp/public/section/calendar/getholiday';
	    	$.ajax({
				url:url,
				type: "post",
				data: {
					'url':'getholiday',
					'mothHoliday':'2022-12',
					'currentLang':'2',
				},
				dataType: "json",
				success: function(data){
					//alert(data);
					/*for(var i=0;i<data.length;i++){
				    		holidayList+='<li class="collection-item avatar">';
				    		holidayList+='<i class="mdi mdi-calendar circle red dark-2">Thu</i><span class="title">'+data[i].holiday_day+'-'+data[i].holiday_string+'</span>';
				    		holidayList+='<p>'+data[i].holiday_name+'</p>';
				    		holidayList+='</li>';
					}*/
					
					 //$("#holidayList").append(holidayList);
					 //setTimeout(function () {
						//	$("#preLoadRecord").removeClass("active");
						//}, 50);
					// return holidayList=19;
				},
				error: function(err) {
					// alert('err'+err);
					/*setTimeout(function () {
						$("#preLoadRecord").removeClass("active");
					}, 50);*/
				}
			});
     };
    
    Calendar.prototype.drawDays = function() {
    	//alert('click4');
    	holidayList = [19,20,21,22,23,24];
        var startDay = new Date(year, month, 1).getDay(),
//      下面表示这个月总共有几天
            nDays = new Date(year, month + 1, 0).getDate(),
            n = startDay;
       // alert('nDays');
//      清除原来的样式和日期
        for(var k = 0; k <42; k++) {
            days[k].innerHTML = '';
            days[k].id = '';
            days[k].className = '';
        }

        for(var i  = 1; i <= nDays ; i++) {
            days[n].innerHTML = i; 
            n++;
        }
        
        for(var j = 0; j < 42; j++) {
            if(days[j].innerHTML === ""){
                
                days[j].id = "disabled";
                
            }else if(j === day + startDay - 1){
                if((this.options && (month === setDate.getMonth()) && (year === setDate.getFullYear())) || (!this.options && (month === today.getMonth())&&(year===today.getFullYear()))){
                    this.drawHeader(day);
                    days[j].id = "today";
                }
            }
            if(selectedDay){
                if((j === selectedDay.getDate() + startDay - 1)&&(month === selectedDay.getMonth())&&(year === selectedDay.getFullYear())){
                days[j].className = "selected";
                /*days[17].className = "selected";
                days[18].className = "selected";
                days[19].className = "selected";
                days[20].className = "selected";
                if(j==holidayList[j]){
                	 days[j].className = "selected";
                }*/
                //days[18].className = "selected";
                //alert('holidayList'+holidayList)
                //days[holidayList].className = "selected";//this is how to select ted day
                	//alert('selectedd'+selectedDay+'value of j'+j+"days of j"+days[j]);
                //alert('was clicked'+selectedDay.getDate());
                this.drawHeader(selectedDay.getDate());
                }
            }
        }
    };
    
    
    Calendar.prototype.clickDay = function(o) {
    	//alert('click1');
        var selected = document.getElementsByClassName("selected"),
            len = selected.length;
        if(len !== 0){
            selected[0].className = "";
        }
        o.className = "selected";
        selectedDay = new Date(year, month, o.innerHTML);
        this.drawHeader(o.innerHTML);
        this.setCookie('selected_day', 1);
        
    };
    
    Calendar.prototype.preMonth = function() {
        if(month < 1){ 
            month = 11;
            year = year - 1; 
        }else{
            month = month - 1;
        }
        this.drawHeader(1);
        this.drawDays();
    };
    
    Calendar.prototype.nextMonth = function() {
        if(month >= 11){
            month = 0;
            year =  year + 1; 
        }else{
            month = month + 1;
        }
        this.drawHeader(1);
        this.drawDays();
    };
    
    Calendar.prototype.getOptions = function() {
        if(this.options){
            var sets = this.options.split('-');
                setDate = new Date(sets[0], sets[1]-1, sets[2]);
                day = setDate.getDate();
                year = setDate.getFullYear();
                month = setDate.getMonth();
        }
    };
    
     Calendar.prototype.reset = function() {
         month = today.getMonth();
         year = today.getFullYear();
         day = today.getDate();
         this.options = undefined;
         this.drawDays();
     };
    
    Calendar.prototype.setCookie = function(name, expiredays){
    	
        if(expiredays) {
            var date = new Date();
            date.setTime(date.getTime() + (expiredays*24*60*60*1000));
            var expires = "; expires=" +date.toGMTString();
        }else{
            var expires = "";
        }
        document.cookie = name + "=" + selectedDay + expires + "; path=/";
        //alert('click2'+selectedDay+'expires'+expires);
    };
    
    Calendar.prototype.getCookie = function(name) {
    	
        if(document.cookie.length){
            var arrCookie  = document.cookie.split(';'),
                nameEQ = name + "=";
            for(var i = 0, cLen = arrCookie.length; i < cLen; i++) {
                var c = arrCookie[i];
                while (c.charAt(0)==' ') {
                    c = c.substring(1,c.length);
                    
                }
                if (c.indexOf(nameEQ) === 0) {
                    selectedDay =  new Date(c.substring(nameEQ.length, c.length));
                }
            }
        }
        //alert('click3'+selectedDay);
    };
    var calendar = new Calendar();
    
        
}, false);

})(jQuery);
