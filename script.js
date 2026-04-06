// Theme Management
function setTheme(theme) {
    if (theme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
        const btn = document.getElementById('darkModeToggle');
        if (btn) {
            btn.innerHTML = '<i class="fas fa-sun"></i>';
            btn.classList.add('active');
        }
    } else {
        document.documentElement.removeAttribute('data-theme');
        localStorage.setItem('theme', 'light');
        const btn = document.getElementById('darkModeToggle');
        if (btn) {
            btn.innerHTML = '<i class="fas fa-moon"></i>';
            btn.classList.remove('active');
        }
    }
}

function toggleDarkMode() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    if (currentTheme === 'dark') {
        setTheme('light');
    } else {
        setTheme('dark');
    }
    
    const cards = document.querySelectorAll('.card, .account-card, .form-container');
    cards.forEach(card => {
        card.style.transform = 'scale(0.98)';
        setTimeout(() => {
            card.style.transform = 'scale(1)';
        }, 200);
    });
}

function loadThemePreference() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        setTheme('dark');
    } else {
        setTheme('light');
    }
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Calendar functionality
let currentDate = new Date();

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
    const monthYearElem = document.getElementById('currentMonthYear');
    if (monthYearElem) {
        monthYearElem.innerHTML = `<i class="fas fa-calendar-alt"></i> ${monthNames[month]} ${year}`;
    }
    
    const firstDayOfMonth = new Date(year, month, 1);
    const lastDayOfMonth = new Date(year, month + 1, 0);
    const startingDayOfWeek = firstDayOfMonth.getDay();
    const totalDaysInMonth = lastDayOfMonth.getDate();
    
    const calendarGrid = document.getElementById('calendarGrid');
    if (!calendarGrid) return;
    
    calendarGrid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Loading calendar...</div>';
    
    const url = `get_calendar_data.php?year=${year}&month=${month + 1}&_=${Date.now()}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            calendarGrid.innerHTML = '';
            
            for (let i = 0; i < startingDayOfWeek; i++) {
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'calendar-day empty';
                calendarGrid.appendChild(emptyDiv);
            }
            
            const today = new Date();
            const todayDate = today.getDate();
            const todayMonth = today.getMonth();
            const todayYear = today.getFullYear();
            
            for (let day = 1; day <= totalDaysInMonth; day++) {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dayData = data[dateStr];
                
                const dayDiv = document.createElement('div');
                dayDiv.className = 'calendar-day';
                
                if (day === todayDate && month === todayMonth && year === todayYear) {
                    dayDiv.classList.add('today');
                }
                
                let html = `<div class="day-number">${day}</div>`;
                
                if (dayData && (dayData.transaction_count > 0 || dayData.total_income > 0 || dayData.total_expense > 0)) {
                    html += '<div class="day-transactions">';
                    
                    if (dayData.transaction_count > 0) {
                        html += `<div class="day-badge">${dayData.transaction_count}</div>`;
                    }
                    if (dayData.total_income > 0) {
                        html += `<div class="day-income"><i class="fas fa-arrow-up"></i> +Rs ${Math.round(dayData.total_income).toLocaleString()}</div>`;
                    }
                    if (dayData.total_expense > 0) {
                        html += `<div class="day-expense"><i class="fas fa-arrow-down"></i> -Rs ${Math.round(dayData.total_expense).toLocaleString()}</div>`;
                    }
                    html += '</div>';
                } else {
                    html += '<div class="day-transactions" style="font-size: 11px; color: #999;">  </div>';
                }
                
                dayDiv.innerHTML = html;
                
                dayDiv.onclick = (function(d) {
                    return function() {
                        dayDiv.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            dayDiv.style.transform = 'scale(1)';
                            if (typeof filterByDate === 'function') {
                                filterByDate(d);
                            }
                        }, 150);
                    };
                })(dateStr);
                
                calendarGrid.appendChild(dayDiv);
            }
        })
        .catch(error => {
            console.error('Calendar error:', error);
            calendarGrid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #e74c3c;">
                                        <i class="fas fa-exclamation-triangle"></i> Error loading calendar.<br>
                                        <small>${error.message}</small>
                                      </div>`;
        });
}

function prevMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
}

function filterByDate(date) {
    if (document.getElementById('filterStartDate')) {
        document.getElementById('filterStartDate').value = date;
        document.getElementById('filterEndDate').value = date;
        if (typeof applyFilters === 'function') {
            applyFilters();
        }
    }
    document.querySelector('.transactions-section')?.scrollIntoView({ behavior: 'smooth' });
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadThemePreference();
    
    if (document.getElementById('calendarGrid')) {
        renderCalendar();
    }
    
    window.onclick = (event) => {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    };
    
    document.querySelectorAll('.close').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.modal').style.display = 'none';
        });
    });
});