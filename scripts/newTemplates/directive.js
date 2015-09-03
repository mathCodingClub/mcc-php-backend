app.directive('{{directive}}', [
  function () {
    return {
      restrict: '{{restrict}}',
      scope: {},
      templateUrl: '{{templateUrl}}',
      link: function ($scope, element, attrs) {        
        
      }
    };
  }]);