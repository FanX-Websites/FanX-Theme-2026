/** JavaScript for Template Parts 
 * 
*/

/* Tab Section Script
* Used in Guest Profiles for Guest Bio and Guest Schedule Tabs */      

function openTab(tabName) {
    var i;
    var x = document.getElementsByClassName("tab");
        for (i = 0; i < x.length; i++) {
            x[i].style.display = "none";  
        }
    document.getElementById(tabName).style.display = "block";  
}
