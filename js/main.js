document.addEventListener('DOMContentLoaded', function() {
    const productos = document.querySelectorAll('li');
    productos.forEach(producto => {
        const producto_id = producto.dataset.productoId;
        actualizarEstrellas(producto_id);
    });
});

// Función para votar
function votar(producto_id, valoracion) {
    axios.post('api/votar.php', { producto_id: producto_id, valoracion: valoracion })
        .then(response => {
            if (response.data === false) {
                alert("Ya has votado este producto.");
            } else {
                actualizarEstrellas(producto_id);
            }
        })
        .catch(error => {
            console.error(error);
        });
}

// Función para eliminar el voto
function eliminarVoto(producto_id) {
    axios.post('api/eliminarVoto.php', { producto_id: producto_id })
        .then(response => {
            actualizarEstrellas(producto_id);
        })
        .catch(error => {
            console.error(error);
        });
}

// Actualizar las estrellas y la media de votos
function actualizarEstrellas(producto_id) {
    axios.get(`api/pintarEstrellas.php?producto_id=${producto_id}`)
        .then(response => {
            const estrellasDiv = document.getElementById(`estrellas-${producto_id}`);
            const ratingInfoDiv = document.getElementById(`rating-info-${producto_id}`);
            const media = response.data.media;
            const votos = response.data.votos;

            // Mostrar las estrellas vacías o rellenas
            estrellasDiv.innerHTML = `<div class="stars"> 
                ${[1, 2, 3, 4, 5].map(star => `
                    <span class="star ${star <= media ? 'active' : ''}" onclick="votar(${producto_id}, ${star})">★</span>
                `).join('')}
            </div>`;

            // Mostrar la media y el número de votos
            ratingInfoDiv.innerHTML = `Valoración: ${media} estrellas (${votos} votos)`;
        })
        .catch(error => {
            console.error(error);
        });
}
