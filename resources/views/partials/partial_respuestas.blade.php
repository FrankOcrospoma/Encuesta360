<style>
    :root {
        --color-primary: #1e4381; /* Azul oscuro */
        --color-secondary: #1e4381; /* Azul claro */
        --color-accent: #00ff00; /* Verde fosforescente */
        --color-light: #f5f5f5; /* Gris claro */
        --color-dark: #343a40; /* Gris oscuro */
    }

    .container {
        margin-top: 50px;
    }

    .card {
        border-radius: 0.5rem;
        box-shadow: 0 2px 5px 0 rgba(0,0,0,0.1);
    }

    .accordion-item {
        margin-bottom: 1rem;
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 2px 5px 0 rgba(0,0,0,0.1);
        transition: box-shadow 0.3s ease-in-out;
    }
    .accordion-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .accordion-button {
        background-color: var(--color-primary);
        color: var(--color-light);
        border: none;
        border-radius: 0.5rem;
        padding: 1rem;
        width: 100%;
        height: 2%;
        text-align: left;
        cursor: pointer;
        transition: background-color 0.3s ease-in-out;
    }
    .accordion-button:focus {
        outline: none;
    }
    .accordion-button:not(.collapsed) {
        background-color: var(--color-secondary);
    }

    .accordion-body {
        background-color: var(--color-light);
        padding: 1rem;
        border-radius: 0 0 0.5rem 0.5rem;
        font-size: 16px;
    }

    .form-check {
        margin-bottom: 1rem;
    }

    .form-check-label {
        display: block;
        margin-bottom: 0.5rem;
    }

    .form-control {
        width: 100%;
        padding: 0.5rem;
        border-radius: 0.25rem;
        border: 1px solid #ccc;
        font-size: 16px;
    }
    button {
  
    font-size: small;
    
}

</style>

<div class="container">
    <div class="card">
        <div class="card-body">
            <div class="accordion" id="accordionExample">
                <?php foreach ($detallesp as $pregunta): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" aria-expanded="false">
                                <?php echo $pregunta->Pregunta; ?>
                            </button>
                        </h2>
                        <div class="accordion-collapse">
                            <div class="accordion-body">
                                <?php if ($pregunta->Pregunta->estado): ?>
                                    <div class="form-check">
                                        <label class="form-check-label"><?php echo $pregunta->Respuesta; ?></label>
                                    </div>
                                <?php else: ?>
                                    <textarea class="form-control" rows="4"><?php echo $pregunta->Respuesta; ?></textarea>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
