/*
----------------------------------------------
    : Custom - Dashboard CRM js :
----------------------------------------------
*/
"use strict";
$(document).ready(function() {    
  /* -- Course Slider -- */
$('.course-slider').slick({
    arrows: true,
    dots: false,
    infinite: true,
    adaptiveHeight: true,
    slidesToShow: 1,
    slidesToScroll: 1,
    prevArrow: '<i class="feather icon-arrow-left"></i>',
    nextArrow: '<i class="feather icon-arrow-right"></i>',
    autoplay: true,                // Enables autoplay
    autoplaySpeed: 3000,           // Speed of auto slide in milliseconds (3 seconds)
});

});