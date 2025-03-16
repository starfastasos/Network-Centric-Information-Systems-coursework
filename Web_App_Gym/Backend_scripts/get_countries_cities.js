// countryCity.js

// Function to set up country and city dropdown selectors
function setupCountryCitySelectors(countrySelectId, citySelectId) {
    // Get references to the country and city dropdown elements using their IDs
    const countrySelect = document.getElementById(countrySelectId);
    const citySelect = document.getElementById(citySelectId);

    // Check if the dropdown elements exist in the DOM
    if (!countrySelect || !citySelect) {
        console.error('Invalid IDs for country or city dropdowns');
        return; // Exit the function if the elements are not found
    }

    // Fetch the list of countries from the API
    fetch('https://countriesnow.space/api/v0.1/countries')
        .then(response => response.json()) // Parse the response as JSON
        .then(data => {
            // Check if the API returned an error
            if (data.error) throw new Error(data.msg);

            // Extract the list of countries from the response data
            const countries = data.data;

            // Populate the "Country" dropdown with the fetched countries
            countries.forEach(country => {
                const option = document.createElement('option'); // Create a new option element
                option.value = country.country; // Set the value of the option to the country name
                option.textContent = country.country; // Set the display text of the option to the country name
                countrySelect.appendChild(option); // Add the option to the country dropdown
            });
        })
        .catch(error => {
            // Handle any errors that occur during the fetch operation
            console.error('Error fetching countries:', error);
        });

    // Add an event listener to the country dropdown to handle changes
    countrySelect.addEventListener('change', () => {
        // Get the selected country from the dropdown
        const selectedCountry = countrySelect.value;

        // Clear the city dropdown and reset it to the default option
        citySelect.innerHTML = '<option value="">Select City</option>';

        // If a country is selected, fetch the list of cities for that country
        if (selectedCountry) {
            fetch('https://countriesnow.space/api/v0.1/countries/cities', {
                method: 'POST', // Use POST method to send the selected country
                headers: {
                    'Content-Type': 'application/json', // Set the content type to JSON
                },
                body: JSON.stringify({ country: selectedCountry }), // Send the selected country in the request body
            })
                .then(response => response.json()) // Parse the response as JSON
                .then(data => {
                    // Check if the API returned an error
                    if (data.error) throw new Error(data.msg);

                    // Extract the list of cities from the response data
                    const cities = data.data;

                    // Populate the "City" dropdown with the fetched cities
                    cities.forEach(city => {
                        const option = document.createElement('option'); // Create a new option element
                        option.value = city; // Set the value of the option to the city name
                        option.textContent = city; // Set the display text of the option to the city name
                        citySelect.appendChild(option); // Add the option to the city dropdown
                    });
                })
                .catch(error => {
                    // Handle any errors that occur during the fetch operation
                    console.error('Error fetching cities:', error);
                });
        }
    });
}

// Export the function to the global scope so it can be accessed in the HTML file
window.setupCountryCitySelectors = setupCountryCitySelectors;