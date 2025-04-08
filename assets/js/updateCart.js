// Fonction pour mettre à jour la quantité d'un produit dans le panier
function updateQuantity(productId, quantity) {
    fetch(`/mettre-a-jour-panier/${productId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ quantity: quantity }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Recharger la page pour refléter les changements
        } else {
            alert('Une erreur est survenue lors de la mise à jour.');
        }
    })
    .catch(error => console.error('Erreur :', error));
}
