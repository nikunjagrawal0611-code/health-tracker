const API = "../api/food_entries.php";

function addFood() {
    const data = {
        food_name: document.getElementById("food_name").value.trim(),
        quantity: document.getElementById("quantity").value.trim(),
        meal_type: document.getElementById("meal_type").value,
        calories: parseInt(document.getElementById("calories").value) || 0,
        protein: parseFloat(document.getElementById("protein").value) || 0,
        carbs: parseFloat(document.getElementById("carbs").value) || 0,
        fat: parseFloat(document.getElementById("fat").value) || 0,
        entry_date: document.getElementById("entry_date").value
    };

    if (!data.food_name || !data.meal_type || !data.entry_date) {
        alert("Please fill all required fields (*)");
        return;
    }

    fetch(API, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(resp => {
        alert(resp.message);
        if (resp.success) {
            clearForm();
            loadEntries();
        }
    })
    .catch(err => {
        console.error(err);
        alert("Server error. Check console for details.");
    });
}

function loadEntries(page = 1) {
    fetch(`${API}?page=${page}`, {
        credentials: "include"
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || "Failed to load entries");
            return;
        }

        const container = document.getElementById("entries");
        if (!data.data.length) {
            container.innerHTML = "<p>No entries found.</p>";
            return;
        }

        container.innerHTML = data.data.map(item => 
            `<p>${item.entry_date} - ${item.food_name} (${item.calories} kcal)</p>`
        ).join("");
    })
    .catch(err => {
        console.error(err);
        alert("Server error while loading entries.");
    });
}

function clearForm() {
    document.getElementById("food_name").value = '';
    document.getElementById("quantity").value = '';
    document.getElementById("meal_type").value = '';
    document.getElementById("calories").value = '';
    document.getElementById("protein").value = '';
    document.getElementById("carbs").value = '';
    document.getElementById("fat").value = '';
    document.getElementById("entry_date").valueAsDate = new Date();
}

// Initial load
document.getElementById("entry_date").valueAsDate = new Date();
loadEntries();