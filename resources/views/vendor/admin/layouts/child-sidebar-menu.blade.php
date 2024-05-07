<li class="list-divider"></li>
<li class="nav-small-cap"><span class="hide-menu">{{ __('CRUD Menu') }}</span></li>
@foreach(\EasyPanel\Models\CRUD::active() as $crud)
    <x-easypanel::crud-menu-item :crud="$crud" />
@endforeach
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

<!-- Incluir CSS de Select2 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />


<!-- Incluir JS de Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

@if ( auth()->user() && auth()->user()->empresa_id == null)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Busca todos los enlaces dentro de la navegación de la barra lateral
        const sidebarLinks = document.querySelectorAll('#sidebarnav a');
    
        sidebarLinks.forEach(link => {
            // Verifica si el enlace contiene el texto "Personals"
            if (link.textContent.trim() === 'Personals') {
                // Sube en la jerarquía del DOM hasta el elemento 'li' y lo oculta
                let listItem = link.parentElement;
                while (listItem && listItem.tagName !== 'LI') {
                    listItem = listItem.parentElement;
                }
                if (listItem) {
                    listItem.style.display = 'none';
                }
            }
        });
    });
    </script>
    
@endif
