function buyCard(cardId) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            alert(this.responseText);
        }
    };
    xhttp.open("GET", "buy_card.php?card_id=" + cardId, true);
    xhttp.send();
}

document.addEventListener('DOMContentLoaded', function() {
    // Get the menu button and navigation menu elements
    var menuButton = document.getElementById('menu-button');
    var navigationMenu = document.getElementById('navigation-menu');

    // Event listener for the menu button click
    menuButton.addEventListener('click', function() {
        // Toggle the 'hidden' class to show/hide the navigation menu
        navigationMenu.classList.toggle('hidden');
    });
});

function filterCards() {
    let checkedTypes = Array.from(document.querySelectorAll('.type-filter:checked')).map(cb => cb.value.toLowerCase());

    document.querySelectorAll('.card').forEach(function(card) {
        // Extract the type from the text content of the 'type' paragraph within the card
        let typeParagraph = card.querySelector('.type');
        let cardType = typeParagraph ? typeParagraph.textContent.trim().toLowerCase() : '';

        // Determine if the card type is in the list of checked types
        let isTypeMatch = checkedTypes.length === 0 || checkedTypes.includes(cardType);

        // Show or hide the card based on whether it matches the checked types
        card.style.display = isTypeMatch ? '' : 'none';
    });
}

// Attach event listeners to checkboxes
document.querySelectorAll('.type-filter').forEach(function(checkbox) {
    checkbox.addEventListener('change', filterCards);
});

// Call filterCards initially to apply the filter on page load
filterCards();

// Global variable to store the random card ID
var randomCardId = null;

// Function to buy a random card
function buyRandomCard() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var card = JSON.parse(this.responseText);

            // Directly initiate the purchase of the random card
            buyCard(card.card_id);
        }
    };
    xhttp.open("GET", "get_random_card.php", true);
    xhttp.send();
}