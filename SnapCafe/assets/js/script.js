'use strict';



/**
 * PRELOAD
 * 
 * loading will be end after document is loaded
 */

const preloader = document.querySelector("[data-preaload]");

window.addEventListener("load", function () {
  preloader.classList.add("loaded");
  document.body.classList.add("loaded");
});



/**
 * add event listener on multiple elements
 */

const addEventOnElements = function (elements, eventType, callback) {
  for (let i = 0, len = elements.length; i < len; i++) {
    elements[i].addEventListener(eventType, callback);
  }
}



/**
 * NAVBAR
 */

const navbar = document.querySelector("[data-navbar]");
const navTogglers = document.querySelectorAll("[data-nav-toggler]");
const overlay = document.querySelector("[data-overlay]");

const toggleNavbar = function () {
  navbar.classList.toggle("active");
  overlay.classList.toggle("active");
  document.body.classList.toggle("nav-active");
}

addEventOnElements(navTogglers, "click", toggleNavbar);



/**
 * HEADER & BACK TOP BTN
 */

const header = document.querySelector("[data-header]");
const backTopBtn = document.querySelector("[data-back-top-btn]");

let lastScrollPos = 0;

const hideHeader = function () {
  const isScrollBottom = lastScrollPos < window.scrollY;
  if (isScrollBottom) {
    header.classList.add("hide");
  } else {
    header.classList.remove("hide");
  }

  lastScrollPos = window.scrollY;
}

window.addEventListener("scroll", function () {
  if (window.scrollY >= 50) {
    header.classList.add("active");
    backTopBtn.classList.add("active");
    hideHeader();
  } else {
    header.classList.remove("active");
    backTopBtn.classList.remove("active");
  }
});



/**
 * HERO SLIDER
 */

const heroSlider = document.querySelector("[data-hero-slider]");
const heroSliderItems = document.querySelectorAll("[data-hero-slider-item]");
const heroSliderPrevBtn = document.querySelector("[data-prev-btn]");
const heroSliderNextBtn = document.querySelector("[data-next-btn]");

let currentSlidePos = 0;
let lastActiveSliderItem = heroSliderItems[0];

const updateSliderPos = function () {
  lastActiveSliderItem.classList.remove("active");
  heroSliderItems[currentSlidePos].classList.add("active");
  lastActiveSliderItem = heroSliderItems[currentSlidePos];
}

const slideNext = function () {
  if (currentSlidePos >= heroSliderItems.length - 1) {
    currentSlidePos = 0;
  } else {
    currentSlidePos++;
  }

  updateSliderPos();
}

heroSliderNextBtn.addEventListener("click", slideNext);

const slidePrev = function () {
  if (currentSlidePos <= 0) {
    currentSlidePos = heroSliderItems.length - 1;
  } else {
    currentSlidePos--;
  }

  updateSliderPos();
}

heroSliderPrevBtn.addEventListener("click", slidePrev);

/**
 * auto slide
 */

let autoSlideInterval;

const autoSlide = function () {
  autoSlideInterval = setInterval(function () {
    slideNext();
  }, 7000);
}

addEventOnElements([heroSliderNextBtn, heroSliderPrevBtn], "mouseover", function () {
  clearInterval(autoSlideInterval);
});

addEventOnElements([heroSliderNextBtn, heroSliderPrevBtn], "mouseout", autoSlide);

window.addEventListener("load", autoSlide);



/**
 * PARALLAX EFFECT
 */

const parallaxItems = document.querySelectorAll("[data-parallax-item]");

let x, y;

window.addEventListener("mousemove", function (event) {

  x = (event.clientX / window.innerWidth * 10) - 5;
  y = (event.clientY / window.innerHeight * 10) - 5;

  // reverse the number eg. 20 -> -20, -5 -> 5
  x = x - (x * 2);
  y = y - (y * 2);

  for (let i = 0, len = parallaxItems.length; i < len; i++) {
    x = x * Number(parallaxItems[i].dataset.parallaxSpeed);
    y = y * Number(parallaxItems[i].dataset.parallaxSpeed);
    parallaxItems[i].style.transform = `translate3d(${x}px, ${y}px, 0px)`;
  }

});



function showRegistrationForm() {
  var form = document.getElementById('registrationForm');
  if (form.style.display === "none") {
      form.style.display = "block";
      showUserTypeFields();
  } else {
      form.style.display = "none";
  }
}
function showUserTypeFields() {
  // Get the user type select element
  var userType = document.getElementById('register-usertype').value;

  // Hide all specific fields and remove required attribute
  var customerFields = document.getElementById('customerFields');
  var employeeFields = document.getElementById('employeeFields');
  customerFields.style.display = "none";
  employeeFields.style.display = "none";
  Array.from(customerFields.getElementsByTagName('input')).forEach(function(input) {
    input.required = false;
  });
  Array.from(employeeFields.getElementsByTagName('input')).forEach(function(input) {
    input.required = false;
  });

  // Show fields based on user type and add required attribute
  if (userType === "customer") {
    customerFields.style.display = "block";
    Array.from(customerFields.getElementsByTagName('input')).forEach(function(input) {
      input.required = true;
    });
  } else if (userType === "employee") {
    employeeFields.style.display = "block";
    Array.from(employeeFields.getElementsByTagName('input')).forEach(function(input) {
      input.required = true;
    });
  }
}




// Login Logout


// Check if user is logged in when the page loads
document.addEventListener('DOMContentLoaded', () => {
  const name = localStorage.getItem('name');
  const loggedIn = localStorage.getItem('loggedIn');
  if (loggedIn === 'true') {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('welcome').style.display = 'block';
    document.getElementById('welcomeMessage').textContent = 'Welcome, ' + name;
  }
});

  // Log out function
  function logout() {
    fetch('server.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'logout=true',
    })
    .then(response => response.text())
    .then(data => {
      if (data.trim() === 'success') {
        localStorage.removeItem('name');
        document.getElementById('loginForm').style.display = 'block';
        document.getElementById('welcome').style.display = 'none';
      }
    });
  }

// Handle form submission
function login(event) {
  event.preventDefault();

  const name = document.getElementById('login-name').value;
  const password = document.getElementById('login-password').value;
  const usertype = document.getElementById('login-usertype').value;

  // Send a POST request to the server
  fetch('server.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `login=true&name=${encodeURIComponent(name)}&password=${encodeURIComponent(password)}&usertype=${encodeURIComponent(usertype)}`,
  })
  .then(response => response.text())
  .then(data => {
    if (data.trim() === 'success') {
      // If login is successful, save the user's name and login status in localStorage
      localStorage.setItem('name', name);
      localStorage.setItem('loggedIn', 'true');
      // Redirect to index.php
      window.location.href = 'index.php';
    } else {
      // If login failed, show an error message
      alert('Invalid name or password');
    }
  });
}

//order
function updateCost(select) {
  const costInput = document.getElementById('cost');
  const selectedOption = select.options[select.selectedIndex];
  const cost = selectedOption.value.split(' - ')[1];
  costInput.value = cost;
    }