jQuery(document).ready(function($) {
  let mql = window.matchMedia("(min-width: 768px)");
  let minItems = mql.matches ? 3 : 2;
  let itemMargin = mql.matches ? 20 : 8;
  $('.autoship-scheduled-order-upsell.flexslider').flexslider({
    animation: "slide",
    animationLoop: false,
    itemWidth: 315,
    itemMargin: itemMargin,
    minItems: minItems,
    maxItems: 3,
    selector: 'ul > li',
    controlNav: false,
    customDirectionNav: $('.autoship-flex-direction-nav a')
  });
});

