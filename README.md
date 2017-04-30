# ProjectCuradores
Plugin para habilitar o role Curadores para as convocatorias.

Estes usuarios podem ver as inscrições, mas não podem editar o projeto

## Ativação

Para ativar este plugin, adicione a seu config.php

```PHP

'plugins' => [

    //... other plugin you may have...
    'ProjectCuradores' => [
        'namespace' => 'ProjectCuradores',
    ],
],

```
