var $vas = jQuery.noConflict();
(function( $vas ) {
    'use strict';

    $vas(function() {

      $vas(".wcmamtx_dismiss_dashboard_text_notice").on('click',function(event) {
        
        event.preventDefault();

        $vas(".wcmamtx_notice_div").remove();

        $vas.ajax({
                data: {action: "wcmamtx_dismiss_dashboard_text_notice"  },
                type: 'POST',
                url: wcmamtxfrontend.ajax_url,
                success: function( response ) { 
                    
                }
        });

        return false;

      });


      $vas(".wcmamtx_dismiss_dashboard_text_notice2").on('click',function(event) {
        
        event.preventDefault();

        $vas(".wcmamtx_notice_div2").remove();

        $vas.ajax({
                data: {action: "wcmamtx_dismiss_dashboard_text_notice2"  },
                type: 'POST',
                url: wcmamtxfrontend.ajax_url,
                success: function( response ) { 
                    
                }
        });

        return false;

      });
        
      $vas(".wcmamtx_group").on('click',function(event) {
          event.preventDefault();

          var parentli = $vas(this).parents("li");

          if (parentli.hasClass("open")) {
             parentli.find("ul.wcmamtx_sub_level").hide();
             parentli.removeClass("open");
             parentli.addClass("closed");
             parentli.find("i.wcmamtx_group_fa").removeClass("fa-chevron-up").addClass("fa-chevron-down");

          } else if (parentli.hasClass("closed")) {

             $vas("li.wcmamtx_group2.open").find("ul.wcmamtx_sub_level").hide();
             $vas("li.wcmamtx_group2.open").find('.wcmamtx_group_fa').removeClass("fa-chevron-up").addClass("fa-chevron-down");
             $vas("li.wcmamtx_group2.open").removeClass("open").addClass("closed");
             


             parentli.find("ul.wcmamtx_sub_level").show();
             parentli.removeClass("closed");
             parentli.addClass("open");

             parentli.find("i.wcmamtx_group_fa").removeClass("fa-chevron-down").addClass("fa-chevron-up");
          }

          return false;
          
      });

        $vas(".wcmamtx_group_sub").on('click',function(event) {
          event.preventDefault();

          var parentli = $vas(this).parents("li");

          if (parentli.hasClass("open")) {
             parentli.find("ul.wcmamtx_sub_level").hide();
             parentli.removeClass("open");
             parentli.addClass("closed");
             parentli.find("i.wcmamtx_group_fa").removeClass("fa-chevron-up").addClass("fa-chevron-down");

          } else if (parentli.hasClass("closed")) {

             $vas("li.wcmamtx_group2_sub.open").find("ul.wcmamtx_sub_level").hide();
             $vas("li.wcmamtx_group2_sub.open").find('.wcmamtx_group_fa').removeClass("fa-chevron-up").addClass("fa-chevron-down");
             $vas("li.wcmamtx_group2_sub.open").removeClass("open").addClass("closed");
             


             parentli.find("ul.wcmamtx_sub_level").show();
             parentli.removeClass("closed");
             parentli.addClass("open");

             parentli.find("i.wcmamtx_group_fa").removeClass("fa-chevron-down").addClass("fa-chevron-up");
          }

          return false;
          
      });



    $vas('.wcmamtx_upload_avatar').on('click', function(event) {
       event.preventDefault();
       $vas('#mywcmamtx_modal').show();
       return false;
    });
    

    $vas('.wcmamtx_modal_close').on('click', function() {
       $vas('#mywcmamtx_modal').hide();
    });

    
});

 
})( jQuery );

// Toggle the visibility of a dropdown menu
const toggleDropdown = (dropdown, menu, isOpen) => {
  dropdown.classList.toggle("open", isOpen);
  menu.style.height = isOpen ? `${menu.scrollHeight}px` : 0;
};

// Close all open dropdowns
const closeAllDropdowns = () => {
  document.querySelectorAll(".dropdown-container.open").forEach((openDropdown) => {
    toggleDropdown(openDropdown, openDropdown.querySelector(".dropdown-menu"), false);
  });
};

// Attach click event to all dropdown toggles
document.querySelectorAll(".dropdown-toggle").forEach((dropdownToggle) => {
  dropdownToggle.addEventListener("click", (e) => {
    e.preventDefault();

    const dropdown = dropdownToggle.closest(".dropdown-container");
    const menu = dropdown.querySelector(".dropdown-menu");
    const isOpen = dropdown.classList.contains("open");

    closeAllDropdowns(); // Close all open dropdowns
    toggleDropdown(dropdown, menu, !isOpen); // Toggle current dropdown visibility
  });
});

// Attach click event to wcmamtx_sidebar toggle buttons
document.querySelectorAll(".wcmamtx_sidebar-toggler, .wcmamtx_sidebar-menu-button").forEach((button) => {
  button.addEventListener("click", () => {
    closeAllDropdowns(); // Close all open dropdowns
    document.querySelector(".wcmamtx_sidebar").classList.toggle("collapsed"); // Toggle collapsed class on wcmamtx_sidebar
  });
});

// Collapse wcmamtx_sidebar by default on small screens
if (window.innerWidth <= 1024) document.querySelector(".wcmamtx_sidebar").classList.add("collapsed");