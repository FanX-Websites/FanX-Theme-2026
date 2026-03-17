/* Admin side Javascript code
 */

//Appearance > Menus 

//Menu Deletion Confirmation Message 
    document.addEventListener('DOMContentLoaded', function() {
        const deleteLinks = document.querySelectorAll('a[aria-label*="Delete"]');
        
        deleteLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to DELETE this menu?')) {
                    e.preventDefault();
                }
            });
        });
    });