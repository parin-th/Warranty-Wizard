let step = 0;  // Variable to track the tutorial step

function tutorialone() {
    const steps = [
        {
            element: document.querySelector('.btn'),  // Add Item button
            message: "Click here to add a new item.",
            action: function() {
                // Ensure the form is still hidden for step 1
                const form = document.getElementById('addItemForm');
                if (form) {
                    form.classList.add('hidden');
                } else {
                    console.error('Form element not found in step 1!');
                }
            }
        },
        {
            element: document.querySelector('#addItemForm'),  // Form itself
            message: "This is the form where you can add a new item. The form will be pre-filled and submitted automatically.",
            action: function() {
                // Show the form in step 2
                const form = document.getElementById('addItemForm');
                if (form) {
                    form.classList.remove('hidden');  // Show the form

                    // Wait a moment before auto-filling and submitting
                    setTimeout(() => {
                        console.log('Attempting to auto-fill and submit the form...');
                        fillAndSubmitForm();  // Automatically fill and submit the form
                    }, 100);  // Wait 100ms to simulate user typing
                } else {
                    console.error('Form element not found in step 2!');
                }
            }
        }
    ];

    if (step < steps.length) {
        if (steps[step].action) steps[step].action();  // Execute any action tied to the current step
        showTooltipone(steps[step].element, steps[step].message);
        step++;
    } else {
        step = 0;  // Reset the tutorial once finished
        removeTooltips();  // Remove any remaining tooltips
    }
}


function tutorialtwo() {
    const steps = [
        {
            element: document.querySelector('.btn'),  // After form submission, hide the form
            message: "The form is now hidden again. Let's move to the next part.",
            action: function() {
                // Hide the form after step 2
                const form = document.getElementById('addItemForm');
                form.classList.add('hidden');  // Hide the form
            }
        },
        {
            element: document.querySelectorAll('.item a[href*="edit"]')[0],  // Edit link (first item)
            message: "Click the name to edit an existing item."
        },
        {
            element: document.querySelectorAll('.item a[href*="delete"]')[0],  // Delete link (first item)
            message: "Click here to delete the item. Be careful, as this action cannot be undone!"
        },
        {
            element: document.querySelector('#calendar'),  // Calendar section
            message: "Your warranties will be shown on the calendar here."
        },
        {
            element: document.querySelector('.navbar a[href*="register"]'),  // Login button
            message: "Be sure to log out when you are done."
        }
    ];

    if (step < steps.length) {
        if (steps[step].action) steps[step].action();  // Execute any action tied to the current step
        showTooltiptwo(steps[step].element, steps[step].message);
        step++;
    } else {
        step = 0;  // Reset the tutorial once finished
        removeTooltips();  // Remove any remaining tooltips
    }
}


// Function to auto-fill and submit the form
function fillAndSubmitForm() {
    // Log that we're attempting to fill the form
    console.log('Attempting to fill the form...');

    // Try to get form elements
    const itemNameInput = document.querySelector('input[name="name"]');
    const itemLocationInput = document.querySelector('input[name="location"]');
    const itemDateInput = document.querySelector('input[name="date"]');
    const itemPeriodInput = document.querySelector('input[name="period"]');
    const submitButton = document.querySelector('#addItemForm button[type="submit"]');

    // Check if the form elements exist
    if (!itemNameInput) {
        console.error('Name input not found!');
    }
    if (!itemLocationInput) {
        console.error('Location input not found!');
    }
    if (!itemDateInput) {
        console.error('Date input not found!');
    }
    if (!itemPeriodInput) {
        console.error('Period input not found!');
    }
    if (!submitButton) {
        console.error('Submit button not found!');
    }

    // Proceed only if all elements are found
    if (itemNameInput && itemLocationInput && itemDateInput && itemPeriodInput && submitButton) {
        // Autofill the form with given values
        console.log('Filling form fields...');
        itemNameInput.value = "Item 1";
        itemLocationInput.value = "Location 1";

        // Set the current date in the 'date' input
        const currentDate = new Date().toISOString().split('T')[0];  // Format as YYYY-MM-DD
        itemDateInput.value = currentDate;

        itemPeriodInput.value = 12;  // Warranty period in months

        // Simulate form submission
        console.log('Submitting the form...');
        submitButton.click();
    } else {
        console.error('Form submission aborted due to missing elements.');
    }
}


function showTooltipone(element, message) {
    removeTooltips();  // Remove any existing tooltips

    if (element) {
        // Create tooltip element
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.innerHTML = `
            <img src="data:image/jpeg;base64,/" alt="Exclamation Mark" class="exclamation-mark">
            <span class="tooltip-text">${message}</span>
        `;

        // Style the tooltip
        tooltip.style.position = 'absolute';
        tooltip.style.top = `${element.getBoundingClientRect().top + window.scrollY - 50}px`;
        tooltip.style.left = `${element.getBoundingClientRect().left + window.scrollX}px`;
        tooltip.style.zIndex = 1000;

        document.body.appendChild(tooltip);

        // Move to next step after a delay
        setTimeout(() => {
            tutorialone();
        }, 3000);
    }
}

function removeTooltips() {
    document.querySelectorAll('.tooltip').forEach(t => t.remove());  // Remove all tooltips
}

// CSS for tooltip styling
const style = document.createElement('style');
style.innerHTML = `
    .tooltip {
        background-color: yellow;
        padding: 10px;
        border: 1px solid black;
        border-radius: 5px;
        display: flex;
        align-items: center;
    }
    .exclamation-mark {
        width: 30px;
        height: 30px;
        margin-right: 10px;
    }
    .tooltip-text {
        font-family: 'Roboto', sans-serif;
        font-size: 14px;
    }
`;
document.head.appendChild(style);
