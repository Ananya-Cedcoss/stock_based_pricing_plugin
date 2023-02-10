jQuery(document).ready(function($) {

    $('.wps-banner__carousel').slick({
        infinite: false,
        loop: false,
        slidesToShow: 5,
        dots:true,
        swipeToSlide: true,
        arrows: true,
        responsive: [{
                breakpoint: 1367,
                settings: {
                    infinite: true,
                    slidesToShow: 4,
                    swipeToSlide: true,
                },
            },
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    swipeToSlide: true,
                },
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2,
                    swipeToSlide: true,
                },
            },
            {
                breakpoint: 520,
                settings: {
                    slidesToShow: 1,
                    swipeToSlide: true,
                },
            },
        ],
    });

});