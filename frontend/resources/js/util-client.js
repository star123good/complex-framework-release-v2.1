/**
 *      main util classes
 */

// Date Time Class
const DateClient = function(strDateTime=null) {
    if (strDateTime == null) this.date = new Date();
    else this.date = new Date(strDateTime);
};

// months
DateClient.months = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December"
];

// get formated time
DateClient.prototype.formatAMPM = function() {
    var hours = this.date.getHours();
    var minutes = this.date.getMinutes();
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    minutes = minutes < 10 ? '0'+minutes : minutes;
    var strTime = hours + ':' + minutes + ' ' + ampm;
    return strTime;
};

// get formated date time
DateClient.prototype.formatDateTime = function() {
    return DateClient.months[this.date.getMonth()] + " " + this.date.getDate() + " " + this.formatAMPM();
};

// get date or time
DateClient.prototype.dateOrTime = function() {
    if(this.date.getTime() > 0) {
        const nowDate = new Date();

        if (this.date.getFullYear() == nowDate.getFullYear()
            && this.date.getMonth() == nowDate.getMonth()
            && this.date.getDate() == nowDate.getDate())
                return this.formatAMPM();
        else    return DateClient.months[this.date.getMonth()] + " " + this.date.getDate();
    }
    else return "";
};

  
// append or remove div
var appendOrRemoveDiv = function($elm, $subElm, isAppend=true) {
    if (!$elm || !$subElm) return ;
    if (isAppend) $elm.append($subElm);
    else $subElm.remove();
    var left = $elm[0].scrollWidth - $elm.width();
    $elm.scrollLeft(left);
};

// show alarm div
var showAlarmDiv = function($elm, message, status='error') {
    $alarmElm = `<div class="notification ${status} closeable">
                    <p>${message}</p>
                    <a class="close" href="#"></a>
                </div>`;
    $elm.prepend($alarmElm);
};


// Hash Class
const HashClient = function(url=null) {
    if (url) {
        var patterns = url.split("#");
        this.hashQuery = (patterns.length > 1) ? patterns[1] : "";
    }
    else {
        this.hashQuery = window.location.hash;
    }
};

// get hash parameter
HashClient.prototype.get = function(key) {
    var matches = this.hashQuery.match(new RegExp(key+'=([^&]*)'));
    return matches ? matches[1] : null;
};

// set hash parameter
HashClient.prototype.set = function(key, value) {
    if (this.hashQuery) {
        var oldValue = this.get(key);
        if (oldValue) {
            this.hashQuery = this.hashQuery.replace(key+'='+oldValue, key+'='+value);
        }
        else {
            this.hashQuery = this.hashQuery + '&' + key + '=' + value;
        }
    }
    else {
        this.hashQuery = '#' + key + '=' + value;
    }
    window.location.hash = this.hashQuery;
};


// get message dom element
var newMessageTemplate = function(data) {
    return `<li class="notifications-not-read" data-id="${data.id}">
                <a href="${BASE_URL}/messages#user=${data.id}">
                    <span class="notification-avatar status-online">
                        <img src="${BASE_URL}${data.avatar}" alt="">
                    </span>
                    <div class="notification-text">
                        <strong>${data.name}</strong>
                        <p class="notification-msg-text">${data.last_msg}</p>
                        <span class="color">${data.last_at_format}</span>
                    </div>
                </a>
            </li>`;
};

// add message alarms
var addNewMessage = function(msg) {
    if (typeof current_user_id === "string" && current_user_id != "" 
        && $("#message-list").length && $("#message-count").length) {
            let data = {
                id : msg.sender,
                receiver : msg.receiver,
                name : msg.name,
                avatar : msg.avatar,
                last_msg : msg.message,
                last_at_format : msg.last_at_format,
            };

            // check if receiver equals to sender
            if (typeof receiver === "object" && receiver.user_id == data.id) return ;

            if ($('[data-id="'+data.id+'"]').length) {
                // update
                $('[data-id="'+data.id+'"]').remove();
            }
            else {
                // add
                let currentNum = parseInt($("#message-count").text());
                if (isNaN(currentNum) || currentNum < 0) currentNum = 0;
                $("#message-count").text(currentNum + 1);
                $("#message-count").removeAttr('hidden');
            }
            $("#message-list").prepend(newMessageTemplate(data));
    }
};

// clear message alarms
var clearNewMessage = function(friendId=null) {
    if (typeof current_user_id === "string" && current_user_id != "" 
        && $("#message-list").length && $("#message-count").length) {
            if (friendId) {
                // clear one friend
                if ($('[data-id="'+friendId+'"]').length) {
                    // remove
                    $('[data-id="'+friendId+'"]').remove();
                    let currentNum = parseInt($("#message-count").text());
                    if (isNaN(currentNum) || currentNum < 2) {
                        $("#message-count").text('');
                        $("#message-count").attr('hidden', 'hidden');
                    }
                    else $("#message-count").text(currentNum - 1);
                }
            }
            else {
                // clear all
                $("#message-count").text('');
                $("#message-count").attr('hidden', 'hidden');
                $("#message-list").html('');
            }
    }
};

// get notification dom element
var newNotificationTemplate = function(data) {
    return `<li class="notifications-not-read" data-id="${data.id}">
                <a href="${BASE_URL}/account/notifications#notification=${data.id}">
                    <span class="notification-icon"><i class="icon-material-outline-access-alarm"></i></span>
                    <span class="notification-text">
                        <strong>${data.sender_name}</strong> ${data.detail} <span class="color">${data.task_title}</span>
                    </span>
                </a>
            </li>`;
};

// add notification alarms
var addNewNotification = function(msg) {
    if (typeof current_user_id === "string" && current_user_id != "" 
        && $("#notification-list").length && $("#notification-count").length) {
            let currentNum = parseInt($("#notification-count").text());
            if (isNaN(currentNum) || currentNum < 0) currentNum = 0;
            $("#notification-count").text(currentNum + 1);
            $("#notification-count").removeAttr('hidden');
            $("#notification-list").prepend(newNotificationTemplate(msg));
            if ($("#notification-count-sidebar").length) {
                $("#notification-count-sidebar").text(currentNum + 1);
                $("#notification-count-sidebar").removeAttr('hidden');
            }
    }
};

// clear notification alarms
var clearNewNotification = function() {
    if (typeof current_user_id === "string" && current_user_id != "" 
        && $("#notification-list").length && $("#notification-count").length) {
            $("#notification-count").text('');
            $("#notification-count").attr('hidden', 'hidden');
            $("#notification-list").html('');
            if ($("#notification-count-sidebar").length) {
                $("#notification-count-sidebar").text('');
                $("#notification-count-sidebar").attr('hidden', 'hidden');
            }
    }
};

// get formatted currency
var getFormattedCurrency = function(currency) {
    if (isNaN(currency)) return null;
    var num = new Number(currency.toFixed(2));
    return num.toLocaleString("en-US", {
            style: "currency",
            currency: "USD"
        });
}


/**
 *      jquery functions
 */
$(document).ready(function() {
    
    // alarm close event handler
    $(document).delegate(".notification.closeable a.close", "click", function() {
        $(this).closest(".notification.closeable").remove();
    });

});