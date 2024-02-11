$(function (){
    $('.reviews-carousel').owlCarousel({
        loop:true,
        margin:10,
        responsiveClass:true,
        items:3,
        dots: false,
        nav: true,
        autoplay: true,
        responsive:{
            0:{
                items:1,
                nav:true,
                loop:false
            },
            600:{
                items:2,
                nav:true,
                loop:false
            },
            1000:{
                items:4,
                nav:true,
                loop:false
            }
        }
    });


});
