<?php
  
namespace ProjectCuradores;

use MapasCulturais\App;

class Plugin extends \MapasCulturais\Plugin {
    
    

    public function _init() {
        
        $app = App::i();
        
        $plugin = $this;
        
        //$app->hook('entity(Registration).canUser(view)', function() use ($app, $plugin) {
        $app->hook('entity(Project).canUser(curate)', function($user, &$result) use ($app, $plugin) {
            
            if (true === $result)
                return true;
            
            $project = $app->view->controller->requestedEntity;
            
            $result = $plugin->checkPermission($this);
            
            
        }, 1000);
        
        $app->hook('entity(Registration).canUser(view)', function($user, &$result) use ($app, $plugin) {
            
            if (true === $result)
                return true;
                
            $result = $plugin->checkPermission($this->project);
            
            
        }, 1000);
        
        
        $app->hook('template(project.single.tabs):end', function() use ($app, $plugin) {
            
            $project = $app->view->controller->requestedEntity;
            
            if (!$project->canUser('@control') && $project->canUser('curate')) {
                echo '<li ng-if="data.projectRegistrationsEnabled"><a href="#inscritos-curador">', \MapasCulturais\i::_e("Inscritos"), '</a></li>';
            }
            
        }, 1000);
        
        $app->hook('template(project.single.tabs-content):end', function() use ($app, $plugin) {
            
            $project = $app->view->controller->requestedEntity;
            
            if (!$project->canUser('@control') && $project->canUser('curate')) {
                
                ?>
                <script>
                $.each(MapasCulturais.entity.registrations, function(i, e) {
                    console.log(e.id);
                    e.status = MapasCulturais.curatorPlugin[e.id];
                });
                </script>
                <?php
                
                echo '<div ng-if="data.projectRegistrationsEnabled" id="inscritos-curador" class="aba-content">';
                $this->part('singles/project-registrations--tables--curator', ['entity' => $project]);
                echo '</div>';
            }
            
        }, 1000);
        
        
        
        //$app->hook('GET(project.single):before', function() use ($app, $plugin) {
        $app->hook('mapasculturais.head', function() use ($app, $plugin) {
            
            $project = $app->view->controller->requestedEntity;

            if (is_object($project) && method_exists($project, 'getClassName') && 
                $project->getClassName() == 'MapasCulturais\Entities\Project' &&
                $project->canUser('curate')
                ) {
                
                $app->view->jsObject['entity']['registrations'] = $project->allRegistrations;
                
                // get the satus
                $app->view->jsObject['curatorPlugin'] = [];
                foreach ($project->allRegistrations as $r) {
                    $app->view->jsObject['curatorPlugin'][$r->id] = $r->status;
                }
            }
            
        }, 1000);
        
        
        
        $app->hook('GET(project.curator_report)', function () use($app){

            $controller = $app->view->controller;
            
            $controller->requireAuthentication();
            $app = App::i();


            if(!key_exists('id', $controller->urlData))
                $app->pass();

            $entity = $controller->requestedEntity;


            if(!$entity)
                $app->pass();


            $entity->checkPermission('curate');

            $app->controller('Registration')->registerRegistrationMetadata($entity);

            $response = $app->response();
            //$response['Content-Encoding'] = 'UTF-8';
            $response['Content-Type'] = 'application/force-download';
            $response['Content-Disposition'] ='attachment; filename=mapas-culturais-dados-exportados.xls';
            $response['Pragma'] ='no-cache';

            $app->contentType('application/vnd.ms-excel; charset=UTF-8');

            ob_start();
            $controller->partial('report', ['entity' => $entity]);
            $output = ob_get_clean();
            echo mb_convert_encoding($output,"HTML-ENTITIES","UTF-8");
        });
        
        
    }
    
    private function checkPermission(\MapasCulturais\Entities\Project $project) {
    
        $curadores = $project->getRelatedAgents('Curadores');
        $user = App::i()->user->profile;
        
        return in_array($user, $curadores);
    
    }

    public function register() {
        
    }
    
}
