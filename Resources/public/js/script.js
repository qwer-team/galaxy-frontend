var galaxy = angular.module("galaxy", ['uiSlider']);

function FlipperCtrl($scope, $http, $timeout) {
    $scope.tooltipMessage = "empty";
    $scope.tooltipStyle = {};
    $scope.pointImagePath = "";
    $scope.pointImage = false;
    $scope.userLog = false;
    $scope.capturedPrize = false;
    $scope.updateUserInfo = function(data){
        $scope.user = data;
        $scope.costActive = 0;
        $scope.costDeposite = 100;
        $scope.costSafe = 0;
        $scope.floor = 0;
        $scope.x = data.gameInfo.x;
        $scope.y = data.gameInfo.y;
        $scope.z = data.gameInfo.z;
        $scope.capturedPrize = false;
        angular.forEach(data.gameInfo.basket, function(value){
            console.log(value);
            if(value.bought == false){
                $scope.capturedPrize = value;
            } 
        });
        if($scope.capturedPrize){
            $http.get('/elements').success(function(data){
                $scope.elements = data;
            });
        }
        if(data.gameInfo.questions.length > 0){
            console.log('questoins');
            $http.get('/question').success(function(data){
                $scope.question = data;
                if(!$scope.questionTimeout){
                    $timeout($scope.updateQuestionTime, 1000);
                    $scope.questionTimeout = true;
                }
                if(data.result != 'fail'){
                    var url = "check/" + $scope.question.id;
                    $http.get(url).success($scope.checkQuestion);
                }
            });
        }
    }
    $scope.questionTimeout = false;
    $scope.checkQuestion = function(data, status){
        if(data.result == null){
            var url = "check/" + $scope.question.id;
            $http.get(url).success($scope.checkQuestion);
        } else {
            $http.get('/user').success($scope.updateUserInfo);
        }
    }
    $scope.updateQuestionTime = function(){
        $scope.question.seconds--;
        if($scope.question.seconds > 0){
            $timeout($scope.updateQuestionTime, 1000);
        }
    }
    $scope.hasMonyeForPrize = function(){
        if(!$scope.elements){
            return false;
        }
        var element = $scope.elements[$scope.capturedPrize.elementId];
        console.log(element, $scope.capturedPrize.id);
        if(element.account == 1){
            if(element.prize > $scope.user.gameInfo.active){
                alert("active "+$scope.user.gameInfo.active);
                return false;
            }
        } else {
            if(element.prize > $scope.user.gameInfo.deposite){
                alert("depo "+$scope.user.gameInfo.depo);
                return false;
            }
        }
        return true;
    }
    $scope.updatePointImage = function(data){
        $scope.pointImagePath = data.pointImagePath;
        $scope.pointImage = true;
    }
  
    $http.get('/user').success($scope.updateUserInfo);
    
    $scope.jumpTooltip = false;
    $scope.jumpTooltipText = "empty";
    $scope.distance = function(){
        if(!$scope.user){
            return 0;
        }
        if(!$scope.checkCoords()){
            return 0;
        }
        var funds;
        if($scope.user.gameInfo.flipper.paymentFromDeposit){
            funds = $scope.user.fundsInfo.deposite;
        } else {
            funds = $scope.user.fundsInfo.active;
        }
        if(funds < $scope.user.gameInfo.flipper.costJump){
            $scope.jumpTooltipText = 'Соберай бабло!';//TODO
            $scope.jumpTooltip = true;
            return 0;
        }
        var dx = $scope.user.gameInfo.x - $scope.x;
        var dy = $scope.user.gameInfo.y - $scope.y;
        var dz = $scope.user.gameInfo.z - $scope.z;
        
        var dist = Math.sqrt(Math.pow(dx, 2) + Math.pow(dy, 2) + Math.pow(dz, 2));
        if(dist > $scope.user.gameInfo.flipper.maxJump){
            var message = 'Превышена допустимая дальность прыжка.';
            var superjumps = $scope.user.gameInfo.superJumps;
            if($scope.user.gameInfo.superJumps > 0){
                message += " Суперпрыжков в наличии: " + superjumps +".";
            }
            $scope.jumpTooltipText = message;//TODO
            $scope.jumpTooltip = true;
        } else{
            $scope.jumpTooltip = false;
        }
        if (dist-Math.floor(dist)==0){
            return dist;
        }
        return dist.toFixed(2);
    }
    $scope.checkCoords = function(){
        var success = true;
        if(isNaN(parseInt($scope.x)) || $scope.x == 0
            || isNaN(parseInt($scope.y)) || $scope.y == 0
            || isNaN(parseInt($scope.z)) || $scope.z == 0){
            success = false;
            $scope.jumpTooltipText = 'Недопустимые коорднаты';//TODO
            $scope.jumpTooltip = true;
        } else {
            $scope.jumpTooltip = false;
        }
        return success;
    }
    $scope.increment = function(varName){
        var coord = $scope[varName];
        coord++;
        if(coord <= $scope.pointMax){
            $scope[varName] = coord;
        }
    }
    $scope.decrement = function(varName){
        var coord = $scope[varName];
        coord--;
        if(coord >= $scope.pointMin){
            $scope[varName] = coord;
        }
    }
    $scope.coordChange = function(old, varName){
        var coord = $scope[varName];
        if(coord > 1000){
            $scope[varName] = parseInt(old);
        }
    }
    $scope.toDeposite = function(value, max){
        console.log(value);
        console.log(max);
        if(parseInt(value) > parseInt(max)){
            $scope.cost = parseInt(max);
        }
    }
    
    
    
    $scope.pointMin = 1;
    $scope.pointMax = 1000;
    $scope.jump = function(){
        var dist = $scope.distance();
        var gameInfo = $scope.user.gameInfo;
        var superjumps = gameInfo.superJumps;
        var superjump = false;
        if( dist > gameInfo.flipper.maxJump ){
            if(superjumps == 0){ // сделать меньше равно
                return;
            } else {
                superjump = true;
            }
        }
        var data = {
            x: parseInt($scope.x), 
            y: parseInt($scope.y), 
            z: parseInt($scope.z), 
            superjump: superjump
        };
        $http.post('/jump', data).success($scope.jumpCallback);
    }
    
    $scope.activeToSafe = function(){
        var data = {
            value: parseInt($scope.costActive) 
        };
        if(parseInt($scope.costActive)> 0){
            $http.post('/store/active_to_safe', data).success($scope.transferCallback);
        } else alert("Значение должно быть > 0");
        
    }
    $scope.safeToActive = function(){
        var data = {
            value: parseInt($scope.costSafe) 
        };
        if(parseInt($scope.costSafe) > 0){
            $http.post('/store/safe_to_active', data).success($scope.transferCallback);
        }else alert("Значение должно быть > 0");
        
    }
    $scope.depositeToActive = function(){
        var data = {
            value: parseInt($scope.costDeposite) 
        };
        $http.post('/store/deposite_to_active', data).success($scope.transferCallback);
    }
    
    $scope.buyMessageCount = function(){
        $http.post('/store/buy_message').success($scope.transferCallback);
    }
    
    $scope.transferCallback = function(data){
        console.log(data);
        if(data.result == 'success'){
           alert("transfer ok");
           $scope.updateUserInfo(data.user);
        }
    }
    $scope.jumpCallback = function(data, status){
        alert(data.pointType);
        if(data.result == 'success'){
            $scope.updateUserInfo(data.user);
            $scope.updatePointImage(data);
            if(data.tag == "black"){
                alert("Через 10 сек разлогинка");
                $timeout(function() {
                    location.reload()
                }, 10000);
            }
        }
    }
    
    $scope.buyElement = function(){
        $http.get('/buyElement').success($scope.buyCallback);
    }
    
    $scope.buyCallback = function(data, status){
        if(data.result == 'success'){
            if(data.prize)
                alert(data.prize);
            $scope.updateUserInfo(data.user);
        }
    };
    $scope.answer = function(){
        if(!$scope.rightAnswer){
            return;
        }
        var url = '/answer/'+$scope.question.id+"/"+$scope.rightAnswer;
        $http.get(url).success($scope.answerResult);
    }
    $scope.answerResult = function(data, status){
        $http.get('/user').success($scope.updateUserInfo);
    }
}

galaxy.directive('leftTooltip', function () {
    return {
        restrict:'A',
        link: function($scope, $element, $attrs)
        {   
            $element.bind('mouseenter', function(e) {
                var left = $element[0].clientWidth + $element[0].offsetLeft + 120;
                var top = $element[0].offsetTop  - 26;
                $scope.tooltipMessage = $attrs.leftTooltip;
                $scope.tooltipStyle = {
                    'top': top+'px', 
                    'left': left+"px", 
                    display: 'block'
                };
                $scope.$apply();
            });
            
            $element.bind('mouseleave', function(e) {
                $scope.tooltipStyle = {};
                $scope.$apply();
            });
        }
    }
});

galaxy.directive('integerInput', function(){
    return {
        link: function($scope, $element, $attrs)
        {   
            $element.bind('keydown', function(event) {
                if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 || 
                    // Allow: Ctrl+A
                    (event.keyCode == 65 && event.ctrlKey === true) || 
                    // Allow: home, end, left, right
                    (event.keyCode >= 35 && event.keyCode <= 39))  {
                    // let it happen, don't do anything
                    return;
                }
                else {
                    // Ensure that it is a number and stop the keypress
                    if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105) && event.keyCode==190) {
                        event.preventDefault(); 
                    }   
                }
            });
        }
    }
});

galaxy.filter('range', function() {
    return function(input, total) {
        total = parseInt(total);
        for (var i=1; i<=total; i++)
            input.push(i);
        return input;
    };
});
