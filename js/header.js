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
  
    // Mobile menu functions
    function openMobileMenu() {
      mobileNav.classList.add("active");
      if (mobileMenuOverlay) {
        mobileMenuOverlay.classList.add("active");
      }
      if (menuIcon) {
        menuIcon.className = "fa fa-times";
      }
      isMenuOpen = true;
      document.body.classList.add("menu-open");
      document.body.style.overflow = "hidden";
    }
  
    function closeMobileMenu() {
      mobileNav.classList.remove("active");
      if (mobileMenuOverlay) {
        mobileMenuOverlay.classList.remove("active");
      }
      if (menuIcon) {
        menuIcon.className = "fa fa-bars";
      }
      isMenuOpen = false;
      document.body.classList.remove("menu-open");
      document.body.style.overflow = "";
      
      // Close all submenus
      $("#mobileNav .sub-menu").removeClass("active");
      $("#mobileNav .sub-toggle").removeClass("active");
    }
  
    // Event listeners
    mobileMenuToggle.addEventListener("click", function (e) {
      e.preventDefault();
      if (isMenuOpen) {
        closeMobileMenu();
      } else {
        openMobileMenu();
      }
    });
  
    if (mobileMenuOverlay) {
      mobileMenuOverlay.addEventListener("click", closeMobileMenu);
    }
  
    if (mobileMenuClose) {
      mobileMenuClose.addEventListener("click", closeMobileMenu);
    }
  
    // Close menu on escape key
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && isMenuOpen) {
        closeMobileMenu();
      }
    });
  
    // Close menu on resize to desktop
    window.addEventListener("resize", function () {
      if (window.innerWidth > 991 && isMenuOpen) {
        closeMobileMenu();
      }
    });
  
    // Submenu toggle functionality
    $(document).on("click", "#mobileNav .sub-toggle", function (e) {
      e.preventDefault();
      const $submenu = $(this).siblings(".sub-menu");
      const isActive = $submenu.hasClass("active");
  
      // Close all other submenus
      $("#mobileNav .sub-menu").not($submenu).removeClass("active");
      $("#mobileNav .sub-toggle").not(this).removeClass("active");
  
      // Toggle current submenu
      if (!isActive) {
        $submenu.addClass("active");
        $(this).addClass("active");
      } else {
        $submenu.removeClass("active");
        $(this).removeClass("active");
      }
    });
  
    // Close menu when clicking a link (mobile only)
    $(document).on("click", "#mobileNav .menu a", function (e) {
      const $link = $(this);
      const $parentItem = $link.closest("li");
      
      // Don't close if it's a submenu toggle or parent with children
      if ($link.siblings(".sub-toggle").length > 0 || 
          ($parentItem.hasClass("menu-item-has-children") && $parentItem.find(".sub-menu").length > 0)) {
        return;
      }
  
      // Close on mobile
      if (window.innerWidth <= 991) {
        setTimeout(closeMobileMenu, 100);
      }
    });
  
    // Desktop dropdown functionality
    $(".ps-dropdown").hover(
      function () {
        $(this).find(".dropdown-menu").fadeIn(200);
      },
      function () {
        $(this).find(".dropdown-menu").fadeOut(200);
      }
    );
  
    // Cart dropdown functionality
    $(".ps-cart").hover(
      function () {
        $(this).find(".ps-cart__listing").fadeIn(200);
      },
      function () {
        $(this).find(".ps-cart__listing").fadeOut(200);
      }
    );
  
    // Touch support for mobile menu
    let touchStartX = 0;
    let touchEndX = 0;
  
    mobileNav.addEventListener("touchstart", function (e) {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
  
    mobileNav.addEventListener("touchend", function (e) {
      touchEndX = e.changedTouches[0].screenX;
      const swipeDistance = touchEndX - touchStartX;
      
      if (swipeDistance > 50 && isMenuOpen) {
        closeMobileMenu();
      }
    }, { passive: true });
  });