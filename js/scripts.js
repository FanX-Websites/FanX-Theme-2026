//Java Script Code for Fun Website Effects 

//Alert Bar - Slide -------------------------------------------------->
//TODO: Alert Bar Code Here         
//END Alert Bar - Slide <--------------------------------------------------

//Mobile Menu - Accordian -------------------------------------------------->
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const mainMenuNav = document.querySelector('.main-menu-nav');

    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            mainMenuNav.classList.toggle('active');
        });
    }
    
    const menuItems = document.querySelectorAll('.dropdown-menu > li');
    
    menuItems.forEach(item => {
        const link = item.querySelector('a');
        const submenu = item.querySelector('ul');
        
        if (submenu) {
            link.addEventListener('click', function(e) {
                // Only prevent default on mobile/tablet (max-width: 1070px)
                if (window.innerWidth < 1070) {
                    e.preventDefault();
                    submenu.classList.toggle('active');
                }
                // On desktop, allow the link to work normally
            });
        }
    });
});
//END Mobile Menu <----------------------------------------------------------

//Countdown Timer ----------------------------------------------------------->
function updateCountdown() {
    const targetDate = new Date(document.querySelector('.countdown-container').dataset.targetDate).getTime();
    const now = new Date().getTime();
    const distance = targetDate - now;

    if (distance > 0) {
        document.getElementById('days').textContent = String(Math.floor(distance / (1000 * 60 * 60 * 24))).padStart(2, '0');
        document.getElementById('hours').textContent = String(Math.floor((distance / (1000 * 60 * 60)) % 24)).padStart(2, '0');
        document.getElementById('minutes').textContent = String(Math.floor((distance / 1000 / 60) % 60)).padStart(2, '0');
        document.getElementById('seconds').textContent = String(Math.floor((distance / 1000) % 60)).padStart(2, '0');
    }
}

updateCountdown();
setInterval(updateCountdown, 1000);
//END Countdown Timer <----------------------------------------------------------