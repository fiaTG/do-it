document.addEventListener("DOMContentLoaded", () => {
    const logo=document.querySelector('.logo');
    const leftLayer = document.querySelector(".outer-layer.left");
    const rightLayer = document.querySelector(".outer-layer.right");
    const bottomImage = document.querySelector(".bottom-image");
    const triangle = document.querySelector(".triangle");
    
    

    if (logo) {
        setTimeout(() => {
            logo.style.opacity = "1";
        }, 1000); // 1 Sekunde Verzögerung
    }
    
    if (leftLayer) {
        setTimeout(() => {
            leftLayer.style.opacity = "1";
        }, 2000); // 1 Sekunde Verzögerung
    }
    
    if (rightLayer) {
        setTimeout(() => {
            rightLayer.style.opacity = "1";
        }, 3000); // 3 Sekunden Verzögerung
    }
    
    if (bottomImage) {
        setTimeout(() => {
            bottomImage.style.opacity = "1";
        }, 4000); // 4 Sekunden Verzögerung
    }
    
    if (triangle) {
        setTimeout(() => {
            triangle.style.opacity = "1";
        }, 5500); // 6 Sekunden Verzögerung
    }
    
});



function toggleIcon(isHovered) {
    const icon = document.getElementById("icon");
    if (isHovered) {
        icon.classList.replace("fa-door-closed", "fa-key");
    } else {
        icon.classList.replace("fa-key", "fa-door-closed");
    }
}


function submitForm() {
    const form = document.getElementById("loginForm");
    if (form) {
        form.submit();
    } else {
        console.error("Formular nicht gefunden!");
    }
}

function toggleRegisterIcon(isHovered) {
    const icon = document.getElementById("register-icon");
    if (!icon) return;

    if (isHovered) {
        icon.classList.replace("fa-user-plus", "fa-user-check");
    } else {
        icon.classList.replace("fa-user-check", "fa-user-plus");
    }
}



