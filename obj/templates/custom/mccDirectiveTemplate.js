angular.module('mcc').directive('{{NAMESPACE}}{{UCFNAME}}',
        ['$routeParams', {{SERVICES}}
        function ($routeParams{{SERVICES-VARS}}) {
        return {
        restrict: '{{RESTRICT}}',
                templateUrl: '{{NAMESPACE}}.{{NAME}}', {{SCOPE}}        
                link: function ($scope, element, attrs) {
                  {{SERVICES-SCOPE}}
                $scope.mcc = {
                routeParams: $routeParams
                };
                }
                };
        }]);

