function openEducationalModal() {
    const modal = document.getElementById("educationalModal");
    const modalContent = document.getElementById("educationalModalContent");

    fetch(`${BASE_URL}/includes/educational_content.php`)
        .then(response => response.text())
        .then(data => {
            modalContent.innerHTML = data;
            modal.style.display = "block";
            document.body.classList.add("no-scroll");
        })
        .catch(error => {
            modalContent.innerHTML = "<p>Error loading content.</p>";
            modal.style.display = "block";
            document.body.classList.add("no-scroll");
        });

    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
            modalContent.innerHTML = "";
            document.body.classList.remove("no-scroll");
        }
    };
}