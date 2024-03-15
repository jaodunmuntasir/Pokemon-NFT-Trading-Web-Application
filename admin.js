// Load cards and set the default view when the page loads
window.onload = function() {
    loadCards();
    showSettings(); // Default to show settings
};

// Function to load cards into the card list
function loadCards() {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        document.getElementById("cards-list").innerHTML = this.responseText;
    }
    xhttp.onerror = function() {
        // Handle errors here
        alert("An error occurred while loading cards.");
    }
    xhttp.open("GET", "load_card.php", true);
    xhttp.send();
}

// Function to handle editing a card
function editCard(cardId) {
    window.location.href = "edit_card.php?id=" + cardId;
}

// Function to handle deleting a card
function deleteCard(cardId) {
    if (confirm("Are you sure you want to delete this card?")) {
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            if(this.status == 200) {
                alert('Card deleted successfully.');
                loadCards(); // Reload the cards
            } else {
                alert('Error deleting card.');
            }
        }
        xhttp.open("GET", "delete_card.php?id=" + cardId, true);
        xhttp.send();
    }
}

// Function to display the settings section
function showSettings() {
    document.getElementById('settings-section').style.display = 'block';
    document.getElementById('cards-section').style.display = 'none';
}

// Function to display the cards section
function showCards() {
    document.getElementById('settings-section').style.display = 'none';
    document.getElementById('cards-section').style.display = 'block';
}

document.addEventListener('DOMContentLoaded', (event) => {
    document.querySelector('.add-card button').addEventListener('click', function() {
        console.log('Add New Card button was clicked');
        openAddCardForm();
    });
});

// Function to open the form for adding a new card
function openAddCardForm() {
    var formHtml = `
        <form action="admin.php" method="post">
            <div class="form-group">
                <label for="name">Card Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="type">Type:</label>
                <input type="text" id="type" name="type" required>
            </div>
            <div class="form-group">
                <label for="hp">HP:</label>
                <input type="number" id="hp" name="hp" required>
            </div>
            <div class="form-group">
                <label for="attack">Attack:</label>
                <input type="number" id="attack" name="attack" required>
            </div>
            <div class="form-group">
                <label for="defense">Defense:</label>
                <input type="number" id="defense" name="defense" required>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="image_url">Image URL:</label>
                <input type="text" id="image_url" name="image_url" required>
            </div>
            <button type="submit" name="add_card">Add Card</button>
        </form>
    `;
    document.getElementById('formContainer').innerHTML = formHtml;
}
