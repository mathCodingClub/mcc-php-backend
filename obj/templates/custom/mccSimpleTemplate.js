angular.module('mcc').directive('mcc{{UCFNAME}}',
        ['$routeParams',
          function ($routeParams) {
            return {
              restrict: '{{RESTRICT}}',
              templateUrl: 'mcc.{{NAME}}',
              scope: false,
              link: function ($scope, element, attrs) {
                $scope.mcc = {
                  routeParams: $routeParams
                };
              }
            };
          }]);

