// JavaScript for handling favorites functionality
document.addEventListener('DOMContentLoaded', function() {
  // Get all heart icons
  const heartIcons = document.querySelectorAll('.heart-icon');
  
  // Add click event listener to each heart icon
  heartIcons.forEach(heart => {
      heart.addEventListener('click', function(event) {
          event.preventDefault();
          
          // Check if user is logged in
          if (!currentUser.isLoggedIn) {
              // Show toast notification
              showToast('Please log in to add favorites', 'error', 'Login Required');
              
              // Optionally redirect to login page
              setTimeout(() => {
                  window.location.href = 'auth.php?mode=login';
              }, 2000);
              
              return;
          }
          
          // Get park ID from data attribute
          const parkId = this.dataset.parkId;
          
          // Add pulse animation
          this.classList.add('heart-pulse');
          
          // Remove animation after it completes
          setTimeout(() => {
              this.classList.remove('heart-pulse');
          }, 300);
          
          // Toggle favorite via AJAX
          toggleFavorite(parkId, this);
      });
  });
  
  // Function to toggle favorite status
  function toggleFavorite(parkId, heartElement) {
      // Create form data
      const formData = new FormData();
      formData.append('park_id', parkId);
      
      // Send AJAX request to toggle favorite
      fetch('toggle_favorite.php', {
          method: 'POST',
          body: formData
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              // Update heart icon appearance
              if (data.is_favorite) {
                  heartElement.classList.add('active');
                  showToast('Added to favorites', 'success');
              } else {
                  heartElement.classList.remove('active');
                  showToast('Removed from favorites', 'success');
              }
          } else {
              showToast(data.message, 'error', 'Error');
          }
      })
      .catch(error => {
          console.error('Error toggling favorite:', error);
          showToast('Failed to update favorites. Please try again.', 'error', 'Error');
      });
  }
});
