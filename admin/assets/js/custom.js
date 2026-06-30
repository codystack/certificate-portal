// Toggle Password Visibility
function togglePassword() {
    const input = document.getElementById("floatingPassword");
    const icon = document.getElementById("toggleIcon");

    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("fe-eye", "fe-eye-off");
    } else {
        input.type = "password";
        icon.classList.replace("fe-eye-off", "fe-eye");
    }
}



//Initialize DataTables
$(document).ready(function() {
    $('#staff').DataTable();
    // $('#orders').DataTable();
    // $('#customers').DataTable();
});


// Greet User
var time = new Date().getHours();
var xgreeting; // Declare the variable properly

if (time < 4) {
    xgreeting = "You should be in bed 🙄!";
} else if (time < 12) {
    xgreeting = "Good morning 🌤"; // wash your hands
} else if (time < 16) {
    xgreeting = "It's lunch 🍛 time "; // what's on the menu!
} else {
    xgreeting = "Good Evening 🌙 "; // how was your day?
}

// Update the greeting in the HTML
document.getElementById("greet").innerHTML = xgreeting;