document.addEventListener("DOMContentLoaded", () => {
    fetch(`${BASE_URL}/processes/get_services.php`)
        .then(res => res.json())
        .then(services => {
            const container = document.getElementById("footerServices");
            if (!container) return;
            container.innerHTML = "";
            services.forEach(service => {
                const p = document.createElement("p");
                p.className = "footerDes";
                p.textContent = service.name;
                container.appendChild(p);
            });
        });

    fetch(`${BASE_URL}/processes/get_branches.php`)
        .then(res => res.json())
        .then(branches => {
            const branchesContainer = document.getElementById("footerBranches");
            const phonesContainer = document.getElementById("footerPhones");

            if (!branchesContainer && !phonesContainer) return;

            if (branchesContainer) branchesContainer.innerHTML = "";
            if (phonesContainer) phonesContainer.innerHTML = "";

            branches.forEach(branch => {

                const a = document.createElement("a");
                a.href = branch.map_url;
                a.textContent = branch.name;
                a.className = "footerDes";
                a.target = "_blank";
                branchesContainer.appendChild(a);
                branchesContainer.appendChild(document.createElement("br"));

                if (phonesContainer) {
                    const p = document.createElement("p");
                    p.className = "footerDes";
                    p.textContent = `${branch.nickname}: ${branch.phone_number}`;
                    phonesContainer.appendChild(p);
                }
            });
        });
});
