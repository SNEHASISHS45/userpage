document.addEventListener('DOMContentLoaded', function() {
    const editBioButton = document.getElementById('edit-bio');
    const bioElement = document.getElementById('bio');

    if (editBioButton) {
        editBioButton.addEventListener('click', function() {
            window.location.href = 'edit_profile.php';
        });
    }

    // Additional JavaScript functionalities can be added here
});

document.addEventListener('DOMContentLoaded', function() {
    // Example JavaScript functionality, add more as needed
});
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('userChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['January', 'February', 'March', 'April', 'May'],
            datasets: [{
                label: 'User Activity',
                data: [12, 19, 3, 5, 2],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
