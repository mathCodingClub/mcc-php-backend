angular.module('mcc').directive('mcc{{UCFNAME}}',
        ['$routeParams',
          function ($routeParams) {
            return {
              restrict: '{{RESTRICT}}',
              templateUrl: 'mcc.{{NAME}}',
              scope: {{SCOPE-MAP}},
              link: function ($scope, element, attrs) {
                $scope.mcc = {
                  routeParams: $routeParams
                };
              }
            };
          }]);

