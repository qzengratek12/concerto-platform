/**
 * Select
 *
 * @param $scope
 * @constructor
 */
function WizardParamSetter3Controller($scope, AdministrationSettingsService) {
  $scope.administrationSettingsService = AdministrationSettingsService;

  if ($scope.output === undefined || typeof $scope.output === 'object') {
    $scope.output = null;
  }
  if ($scope.output == null && $scope.param.definition != undefined) {
    $scope.output = $scope.param.definition.defvalue;
  }
  if ($scope.output === undefined || $scope.output === null) {
    $scope.output = "";
  }
  $scope.onPrimitiveValueChange($scope.output);
};

concertoPanel.controller('WizardParamSetter3Controller', ["$scope", "AdministrationSettingsService", WizardParamSetter3Controller]);