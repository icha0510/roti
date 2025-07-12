$(document).ready(function () {
    // Mobile Menu Functionality
    const mobileMenuToggle = document.getElementById("mobileMenuToggle");
    const mobileMenuOverlay = document.getElementById("mobileMenuOverlay");
    const mobileNav = document.getElementById("mobileNav");
    const mobileMenuClose = document.getElementById("mobileMenuClose");
    const menuIcon = document.getElementById("menuIcon");
    let isMenuOpen = false;
  
    // Check if required elements exist
    if (!mobileMenuToggle || !mobileNav) {
      console.warn('Required mobile menu elements not found');
      return;
    }
  
    // Enhanced mobile menu functions
    function openMobileMenu() {
      if (!mobileNav) return;
      
      mobileNav.classList.add("active");
      if (mobileMenuOverlay) {
        mobileMenuOverlay.classList.add("active");
      }
      if (menuIcon) {
        menuIcon.className = "fa fa-times";
      }
      isMenuOpen = true;
      document.body.classList.add("menu-open");
  
      // Add smooth entrance animation
      mobileNav.style.transform = "translateX(0)";
  
      // Prevent body scroll
      document.body.style.overflow = "hidden";
    }
  
    function closeMobileMenu() {
      if (!mobileNav) return;
      
      mobileNav.classList.remove("active");
      if (mobileMenuOverlay) {
        mobileMenuOverlay.classList.remove("active");
      }
      if (menuIcon) {
        menuIcon.className = "fa fa-bars";
      }
      isMenuOpen = false;
      document.body.classList.remove("menu-open");
  
      // Add smooth exit animation
      mobileNav.style.transform = "translateX(100%)";
  
      // Restore body scroll
      document.body.style.overflow = "";
  
      // Close all submenus when closing main menu
      $("#mobileNav .sub-menu").removeClass("active");
      $("#mobileNav .sub-toggle").removeClass("active");
    }
  
    // Event listeners with enhanced functionality
    mobileMenuToggle.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      if (isMenuOpen) {
        closeMobileMenu();
      } else {
        openMobileMenu();
      }
    });
  
    if (mobileMenuOverlay) {
      mobileMenuOverlay.addEventListener("click", function (e) {
        e.preventDefault();
        closeMobileMenu();
      });
    }
  
    if (mobileMenuClose) {
      mobileMenuClose.addEventListener("click", function (e) {
        e.preventDefault();
        closeMobileMenu();
      });
    }
  
    // Close menu on escape key
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && isMenuOpen) {
        closeMobileMenu();
      }
    });
  
    // Close menu on resize to desktop
    let resizeTimer;
    window.addEventListener("resize", function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        if (window.innerWidth > 991 && isMenuOpen) {
          closeMobileMenu();
        }
      }, 250);
    });
  
    // Enhanced submenu toggle functionality
    $(document).on("click", "#mobileNav .sub-toggle", function (e) {
      e.preventDefault();
      e.stopPropagation();
  
      const $submenu = $(this).siblings(".sub-menu");
      const $parentItem = $(this).closest("li");
      const isActive = $submenu.hasClass("active");
  
      // Close all other submenus first
      $("#mobileNav .sub-menu").not($submenu).removeClass("active");
      $("#mobileNav .sub-toggle").not(this).removeClass("active");
  
      // Toggle current submenu
      if (!isActive) {
        $submenu.addClass("active");
        $(this).addClass("active");
  
        // Smooth scroll to submenu if needed
        setTimeout(() => {
          const submenuHeight = $submenu.outerHeight();
          const containerHeight = mobileNav.clientHeight;
          const submenuOffset = $submenu.offset();
          const containerOffset = $(mobileNav).offset();
          
          if (submenuOffset && containerOffset) {
            const submenuTop = submenuOffset.top;
            const containerTop = containerOffset.top;
  
            if (submenuTop + submenuHeight > containerTop + containerHeight) {
              mobileNav.scrollTo({
                top: Math.max(0, submenuTop - containerTop - 20),
                behavior: "smooth",
              });
            }
          }
        }, 100);
      } else {
        $submenu.removeClass("active");
        $(this).removeClass("active");
      }
    });
  
    // Handle parent menu items with children
    $(document).on("click", "#mobileNav .menu > li.menu-item-has-children > a", function (e) {
      // If this menu item has children, prevent default navigation
      const $parentItem = $(this).closest("li");
      if ($parentItem.find(".sub-menu").length > 0) {
        e.preventDefault();
        // Optionally trigger the sub-toggle
        const $subToggle = $parentItem.find(".sub-toggle").first();
        if ($subToggle.length) {
          $subToggle.trigger("click");
        }
      }
    });
  
    // Close menu when clicking a regular link (mobile only)
    $(document).on("click", "#mobileNav .menu a", function (e) {
      const $link = $(this);
      const $parentItem = $link.closest("li");
      
      // Don't close if it's a submenu toggle
      if ($link.siblings(".sub-toggle").length > 0) {
        return;
      }
  
      // Don't close if it's a parent item with children
      if ($parentItem.hasClass("menu-item-has-children") && $parentItem.find(".sub-menu").length > 0) {
        return;
      }
  
      // Only close on mobile
      if (window.innerWidth <= 991) {
        // Add small delay to show the click effect
        setTimeout(function () {
          closeMobileMenu();
        }, 100);
      }
    });
  
    // Enhanced desktop dropdown functionality
    $(".ps-dropdown").hover(
      function () {
        $(this).find(".dropdown-menu").stop().fadeIn(200);
      },
      function () {
        $(this).find(".dropdown-menu").stop().fadeOut(200);
      }
    );
  
    // Enhanced cart dropdown functionality
    $(".ps-cart").hover(
      function () {
        $(this).find(".ps-cart__listing").stop().fadeIn(200);
      },
      function () {
        $(this).find(".ps-cart__listing").stop().fadeOut(200);
      }
    );
  
    // Enhanced touch support for mobile
    let touchStartX = 0;
    let touchEndX = 0;
  
    if (mobileNav) {
      mobileNav.addEventListener(
        "touchstart",
        function (e) {
          touchStartX = e.changedTouches[0].screenX;
        },
        { passive: true }
      );
  
      mobileNav.addEventListener(
        "touchend",
        function (e) {
          touchEndX = e.changedTouches[0].screenX;
          handleSwipe();
        },
        { passive: true }
      );
    }
  
    function handleSwipe() {
      const swipeThreshold = 50;
      const swipeDistance = touchEndX - touchStartX;
  
      if (swipeDistance > swipeThreshold && isMenuOpen) {
        // Swipe right - close menu
        closeMobileMenu();
      }
    }
  
    // Add focus management for accessibility
    if (mobileMenuToggle) {
      mobileMenuToggle.addEventListener("focus", function () {
        this.setAttribute("aria-expanded", isMenuOpen ? "true" : "false");
      });
    }
  
    // Enhanced keyboard navigation
    $(document).on("keydown", "#mobileNav .menu a", function (e) {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        $(this).trigger("click");
      }
    });
  
    // Add visual feedback for interactions
    $(document).on("mousedown", ".menu-toggle-btn, .mobile-menu-close", function () {
      $(this).addClass("pressed");
    }).on("mouseup mouseleave", ".menu-toggle-btn, .mobile-menu-close", function () {
      $(this).removeClass("pressed");
    });
  
    // Add loading animation for menu items
    $(document).on("click", ".mobile-nav .menu > li > a", function () {
      if (!$(this).siblings(".sub-toggle").length && !$(this).closest("li").hasClass("menu-item-has-children")) {
        $(this).addClass("loading");
        setTimeout(() => {
          $(this).removeClass("loading");
        }, 500);
      }
    });
  
    // Add CSS classes for animations
    $(".mobile-nav").addClass("js-enabled");
    $(".header-nav").addClass("js-enabled");
  
    // Initialize tooltips for cart items (if needed)
    $(".ps-cart-item__close").attr("title", "Remove item");
  
    // Add loading state for menu toggle
    $(document).on("click", ".menu-toggle-btn", function () {
      $(this).addClass("loading");
      setTimeout(() => {
        $(this).removeClass("loading");
      }, 300);
    });
  
    // Initialize menu state
    if (mobileMenuToggle) {
      mobileMenuToggle.setAttribute("aria-expanded", "false");
    }
    
    // Set initial transform for mobile nav
    if (mobileNav && !mobileNav.classList.contains("active")) {
      mobileNav.style.transform = "translateX(100%)";
    }
  });