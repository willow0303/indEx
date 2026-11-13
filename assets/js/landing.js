document.addEventListener("DOMContentLoaded", () => {

    const observerOptions = { threshold: 0.1 };

    const scrollObserver = new IntersectionObserver((entries, observer) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                entry.target.classList.add("animate");
                observer.unobserve(entry.target); // Animate once only
            }
        }
    }, observerOptions);

    document.querySelectorAll(".animate-on-scroll").forEach(el => {
        scrollObserver.observe(el);
    });


    const navToggle = document.querySelector(".nav-toggle");
    const navMenu = document.querySelector(".nav-menu");
    const body = document.body;

    if (navToggle && navMenu) {
        navToggle.addEventListener("click", () => {
            const isOpening = !navMenu.classList.contains("active");

            navToggle.classList.toggle("active", isOpening);
            navMenu.classList.toggle("active", isOpening);

            body.style.overflow = isOpening ? "hidden" : "auto";
        });

        navMenu.querySelectorAll("a").forEach(link => {
            link.addEventListener("click", () => {
                if (navMenu.classList.contains("active")) {
                    navToggle.classList.remove("active");
                    navMenu.classList.remove("active");
                    body.style.overflow = "auto";
                }
            });
        });
    }


    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener("click", e => {
            const href = anchor.getAttribute("href");

            if (!href || href === "#") return;

            e.preventDefault();

            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });
            }
        });
    });

});
