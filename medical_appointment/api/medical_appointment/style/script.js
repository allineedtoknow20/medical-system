var confirmItems = document.querySelectorAll("[data-confirm]");
for (var i = 0; i < confirmItems.length; i++) {
    confirmItems[i].addEventListener("click", function (event) {
        if (!confirm(this.getAttribute("data-confirm"))) {
            event.preventDefault();
        }
    });
}

var registerForm = document.getElementById("registerForm");
if (registerForm) {
    registerForm.addEventListener("submit", function (event) {
        var pass = document.getElementById("password").value;
        var pass2 = document.getElementById("confirm_password").value;
        if (pass.length < 6) {
            alert("Password must be at least 6 characters.");
            event.preventDefault();
        } else if (pass != pass2) {
            alert("Passwords do not match.");
            event.preventDefault();
        }
    });
}

var bookForm = document.getElementById("bookForm");

if (bookForm) {
    var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

    var deptSelect = document.getElementById("department");
    var doctorSelect = document.getElementById("doctor");
    var dateInput = document.getElementById("date");
    var calendar = document.getElementById("calendar");
    var slotBox = document.getElementById("slotBox");

    var today = bookingData.today;
    var last = bookingData.last;
    var viewYear = parseInt(today.substr(0, 4));
    var viewMonth = parseInt(today.substr(5, 2)) - 1;

    function pad(number) {
        return number < 10 ? "0" + number : "" + number;
    }

    function slotInfo(date) {
        var info = {max: 100, used: 0};
        var doctor = bookingData.slots[doctorSelect.value];
        if (doctor && doctor[date]) {
            info = doctor[date];
        }
        info.available = info.max - info.used;
        return info;
    }

    function slotStatus(available) {
        if (available <= 0) {
            return "Fully Booked";
        }
        if (available <= 20) {
            return "Almost Full";
        }
        return "Available";
    }

    function fillDoctors() {
        doctorSelect.innerHTML = "<option value=''>Select doctor</option>";
        for (var i = 0; i < bookingData.doctors.length; i++) {
            var d = bookingData.doctors[i];
            if (d.department_id == deptSelect.value) {
                var option = document.createElement("option");
                option.value = d.doctor_id;
                option.textContent = d.doctor_name;
                doctorSelect.appendChild(option);
            }
        }
    }

    function showSlots() {
        var date = dateInput.value;
        if (date == "" || doctorSelect.value == "") {
            slotBox.style.display = "none";
            return;
        }

        var info = slotInfo(date);
        var status = slotStatus(info.available);

        document.getElementById("slotTotal").textContent = info.max;
        document.getElementById("slotUsed").textContent = info.used;
        document.getElementById("slotAvailable").textContent = info.available;

        var label = document.getElementById("slotStatus");
        label.textContent = status == "Fully Booked" ? "FULLY BOOKED" : status;
        label.className = "status " + status.toLowerCase().replace(" ", "");
        slotBox.style.display = "block";
    }

    function drawCalendar() {
        var firstDay = new Date(viewYear, viewMonth, 1).getDay();
        var total = new Date(viewYear, viewMonth + 1, 0).getDate();
        var monthKey = viewYear + "-" + pad(viewMonth + 1);

        var html = "<div class='cal-head'>";
        html += "<button type='button' id='prevMonth'" + (monthKey <= today.substr(0, 7) ? " disabled" : "") + ">&lsaquo;</button>";
        html += "<span>" + months[viewMonth] + " " + viewYear + "</span>";
        html += "<button type='button' id='nextMonth'" + (monthKey >= last.substr(0, 7) ? " disabled" : "") + ">&rsaquo;</button>";
        html += "</div><div class='cal-grid'>";

        for (var i = 0; i < 7; i++) {
            html += "<div class='dow'>" + days[i] + "</div>";
        }
        for (var i = 0; i < firstDay; i++) {
            html += "<div></div>";
        }

        for (var day = 1; day <= total; day++) {
            var date = monthKey + "-" + pad(day);
            var css = "";
            var disabled = "";

            if (date < today || date > last) {
                css = "past";
                disabled = " disabled";
            } else if (doctorSelect.value != "" && slotInfo(date).available <= 0) {
                css = "full";
                disabled = " disabled";
            } else if (date == dateInput.value) {
                css = "selected";
            }
            html += "<button type='button' class='" + css + "' data-date='" + date + "'" + disabled + ">" + day + "</button>";
        }

        html += "</div><div class='cal-legend'><i></i>Available<i class='sel'></i>Selected</div>";
        calendar.innerHTML = html;

        document.getElementById("prevMonth").onclick = function () {
            viewMonth--;
            if (viewMonth < 0) {
                viewMonth = 11;
                viewYear--;
            }
            drawCalendar();
        };
        document.getElementById("nextMonth").onclick = function () {
            viewMonth++;
            if (viewMonth > 11) {
                viewMonth = 0;
                viewYear++;
            }
            drawCalendar();
        };

        var buttons = calendar.querySelectorAll("button[data-date]");
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].onclick = function () {
                dateInput.value = this.getAttribute("data-date");
                drawCalendar();
                showSlots();
            };
        }
    }

    deptSelect.addEventListener("change", function () {
        fillDoctors();
        showSlots();
        drawCalendar();
    });

    doctorSelect.addEventListener("change", function () {
        if (dateInput.value != "" && slotInfo(dateInput.value).available <= 0) {
            dateInput.value = "";
        }
        showSlots();
        drawCalendar();
    });

    bookForm.addEventListener("submit", function (event) {
        if (dateInput.value == "") {
            alert("Please choose a date on the calendar.");
            event.preventDefault();
        }
    });

    var picked = bookingData.selected;
    if (picked.department) {
        deptSelect.value = picked.department;
        fillDoctors();
        doctorSelect.value = picked.doctor;
    }
    if (dateInput.value != "") {
        viewYear = parseInt(dateInput.value.substr(0, 4));
        viewMonth = parseInt(dateInput.value.substr(5, 2)) - 1;
    }
    drawCalendar();
    showSlots();
}
