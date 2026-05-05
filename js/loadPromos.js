document.addEventListener("DOMContentLoaded", () => {
    const wrapper = document.getElementById("promoWrapper");
    if (!wrapper) return;

    fetch(`${BASE_URL}/processes/get_promos.php`)
        .then(res => res.json())
        .then(promos => {
            wrapper.innerHTML = "";

            if (promos.length === 0) {
                wrapper.innerHTML = `<p class="no-promo">No active promos right now.</p>`;
                return;
            }

            promos.forEach(promo => {
                const slide = document.createElement("div");
                slide.className = "swiper-slide";
                slide.innerHTML = `
                    <div class="promo-card">
                        <img 
                            src="${BASE_URL}${promo.image_path}" 
                            alt="Promo" 
                            class="promo-img" 
                            data-id="${promo.promo_id}"
                            style="cursor: pointer;"
                        >
                        <div class="promo-overlay">
                            <h4>${promo.name}</h4>
                            <p>
                                ${promo.start_date
                                    ? new Date(promo.start_date).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" })
                                    : "No date set"}
                                ${promo.end_date
                                    ? " – " + new Date(promo.end_date).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" })
                                    : ""}
                            </p>
                        </div>
                    </div>
                `;
                wrapper.appendChild(slide);
            });

            let slidesPerView = Math.min(promos.length, 3);
            let enableLoop = promos.length > 3;

            new Swiper(".promo-slider", {
                slidesPerView: slidesPerView,
                spaceBetween: 20,
                loop: enableLoop,
                autoplay: promos.length > 1 ? {
                    delay: 3000,
                    disableOnInteraction: false
                } : false,
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev"
                },
                breakpoints: {
                    0: { slidesPerView: Math.min(promos.length, 1) },
                    768: { slidesPerView: Math.min(promos.length, 2) },
                    1024: { slidesPerView: Math.min(promos.length, 3) }
                }
            });
        })
        .catch(err => {
            console.error("Failed to load promos:", err);
        });

    wrapper.addEventListener("click", (e) => {
        const promoImg = e.target.closest(".promo-img");
        if (promoImg && promoImg.dataset.id) {
            openPromoModal(promoImg.dataset.id);
        }
    });
});

function openPromoModal(promoId) {
    fetch(`${BASE_URL}/processes/get_promo_details.php?id=${promoId}`)
        .then(res => res.json())
        .then(promo => {
            if (!promo || promo.error) {
                console.error("Promo not found or invalid:", promo?.error || "Unknown error");
                return;
            }

            document.getElementById("promoModalImg").src = BASE_URL + promo.image_path;
            document.getElementById("promoTitle").textContent = promo.name;
            document.getElementById("promoDesc").textContent = promo.description || "No description available.";
            document.getElementById("promoBranch").textContent = promo.branch_names 
                ? `Branch Available: ${promo.branch_names}` 
                : "Branch Available: Not specified";

            let discountDisplay = "";
            if (promo.discount_type && promo.discount_value) {
                if (promo.discount_type === "fixed") {
                    discountDisplay = `Discount: ₱${parseFloat(promo.discount_value).toFixed(2)}`;
                } else if (promo.discount_type === "percentage") {
                    discountDisplay = `Discount: ${parseFloat(promo.discount_value)}%`;
                }
            }

            const start = promo.start_date ? new Date(promo.start_date).toLocaleDateString("en-US") : "N/A";
            const end = promo.end_date ? new Date(promo.end_date).toLocaleDateString("en-US") : "N/A";

            let dateDisplay = (promo.start_date || promo.end_date)
                ? `Valid: ${start} – ${end}`
                : "No date set";

            document.getElementById("promoDate").innerHTML = `
                ${discountDisplay ? `<strong>${discountDisplay}</strong><br>` : ""}
                ${dateDisplay}
            `;

            const modal = document.getElementById("promoModal");
            modal.style.display = "flex";
        })
        .catch(err => console.error("Failed to fetch promo details:", err));
}

window.addEventListener("click", (e) => {
    const modal = document.getElementById("promoModal");
    if (e.target === modal) {
        modal.style.display = "none";
    }
});