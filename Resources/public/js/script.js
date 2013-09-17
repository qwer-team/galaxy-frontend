var galaxy = angular.module("galaxy", ['uiSlider']);

function FlipperCtrl($scope, $http, $timeout) {
    $scope.tooltipMessage = "empty";
    $scope.tooltipStyle = {};
    $scope.pointType = 1;
    $scope.pointImagePath = "";
    $scope.pointImage = false;
    $scope.userLog = false;
    $scope.zoneShow = false;
    $scope.zoneShow2 = false;
    $scope.capturedPrize = false;
    $scope.lightAmount = '';
    $scope.jumpActive = false;
    $scope.getActive = false;
    $scope.blackPar = false;
    $scope.blackParameter = 0;
    $scope.unlightAmount = function() {
        $scope.lightAmount = '';
    }
    $scope.updateFundsJs = function(data) {
        $('#active').html(data.fundsInfo.active);
        $('#safe').html(data.fundsInfo.safe);
        $('#deposite').html(data.fundsInfo.deposite);
        $('#active').attr("active", data.fundsInfo.active);
        $('.mark.bank .transfer-count').html('+' + data.fundsInfo.transSafe);
        $('.mark.price .transfer-count').html('+' + data.fundsInfo.transActive);
        $('.mark.bank .transfer-count').attr('transSafe', data.fundsInfo.transSafe);
        $('.mark.price .transfer-count').attr('transActive', data.fundsInfo.transActive);
        if (data.fundsInfo.transSafe == 0) {
            $('.mark.bank .transfer-count:visible').fadeToggle(300);
        }
        if (data.fundsInfo.transSafe == 0) {
            $('.mark.price .transfer-count:visible').fadeToggle(300);
        }
    }
    decValue = function() {
        $scope.active = $scope.active - 1;
        $('#active').html($scope.active);
        $scope.user.fundsInfo.active = $scope.active;
    }

    $scope.updateUserInfo = function(data, status) {
        /* if (status == 401) {
         //$scope.logout();
         }*/
        if ($scope.user) {
            if ($scope.user.fundsInfo.active != data.fundsInfo.active) {
                $scope.updateValue($('#active'));
            }
            if ($scope.user.fundsInfo.safe != data.fundsInfo.safe) {
                $scope.updateValue($('#safe'));
            }
            if ($scope.user.fundsInfo.deposite != data.fundsInfo.deposite) {
                $scope.updateValue($('#deposite'));
            }
        }
        $scope.updateValue = function(obj) {
            obj.delay(500).fadeToggle(300).delay(500).fadeToggle(300).delay(500).fadeToggle(300).delay(500).fadeToggle(300).delay(500).fadeToggle(300).delay(500).fadeToggle(300);
        }
        /*$scope.ololo = data.fundsInfo.active;
         if ($scope.user) {
         $scope.active = $scope.user.fundsInfo.active;
         var id = setInterval("decValue()", 1000);
         var act = parseInt($('#active').attr("active"));
         var unbindWatcher = $scope.$watch(
         "user.fundsInfo.active",
         function(newActive) {
         console.log(act);
         console.log($scope.active);
         if (act == $scope.active) {
         clearInterval(id);
         unbindWatcher();
         }
         }
         );
         }*/

        $scope.user = data;
        $scope.costActive = 0;
        $scope.costDeposite = 100;
        $scope.costSafe = 0;
        $scope.floor = 0;
        $scope.x = data.gameInfo.x;
        $scope.y = data.gameInfo.y;
        $scope.z = data.gameInfo.z;
        $scope.capturedPrize = false;
        $scope.updateFundsJs(data);
        angular.forEach(data.gameInfo.basket, function(value) {
            console.log(value);
            if (value.bought == false) {
                $scope.capturedPrize = value;
            }
        });
        if ($scope.capturedPrize) {
            $http.get('/elements').success(function(data) {
                $scope.elements = data;
            });
        }
        if (data.gameInfo.questions.length > 0) {
            console.log('questoins');
            $http.get('/question').success(function(data) {
                $scope.question = data;
                if (!$scope.questionTimeout) {
                    $timeout($scope.updateQuestionTime, 1000);
                    $scope.questionTimeout = true;
                }
                if (data.result != 'fail') {
                    var url = "check/" + $scope.question.id;
                    $http.get(url).success($scope.checkQuestion);
                }
            });
        }
        if (data.lockedExpiresAt) {
            var lock = new Date(data.lockedExpiresAt.date + " " + data.lockedExpiresAt.timezone);
            var now = new Date();
            if (lock > now) {
                $scope.logout();
            }
        }
    }
    $scope.userError = function(data, status) {
        $scope.logout();
    }
    $scope.questionTimeout = false;
    $scope.checkQuestion = function(data, status) {
        if (data.result == null) {
            var url = "check/" + $scope.question.id;
            $http.get(url).success($scope.checkQuestion);
        } else {
            $scope.getUser();
        }
    }
    $scope.getUser = function() {
        $http.get('/user').success($scope.updateUserInfo)
                .error($scope.userError);
    };
    $scope.updateQuestionTime = function() {
        if ($scope.question.seconds > 0) {
            $scope.question.seconds--;
        }
        $timeout($scope.updateQuestionTime, 1000);
    }
    $scope.hasMonyeForPrize = function() {
        if (!$scope.elements) {
            return false;
        }
        var element = $scope.elements[$scope.capturedPrize.elementId];
        if (element.account == 1) {
            if (element.prize > $scope.user.gameInfo.active) {
                alert("active " + $scope.user.gameInfo.active);
                return false;
            }
        } else {
            if (element.prize > $scope.user.gameInfo.deposite) {
                alert("depo " + $scope.user.gameInfo.depo);
                return false;
            }
        }
        return true;
    }
    $scope.updatePointImage = function(data) {
        var arr = data.image.split('.');
        if (arr[arr.length - 1] == "swf")
        {
            var flashvars = {
            };
            var params = {
                menu: "false"
            };
            var attributes = {
                id: "myContent",
                name: "myContent",
                style: "margin-left:25%"
            };
            var w = 690;
            var h = 550;
            if(data.tag == 'black'){
                w = 1600;
                h = 895;
                $scope.blackPar = true;
                console.log(data.req);
                $scope.blackParameter = data.req.response.subtype.parameter;
            }
            swfobject.embedSWF("/bundles/galaxyfrontend/" + data.image, "myContent", w, h, "9.0.0",
                    "expressInstall.swf", flashvars, params, attributes);
        } else {
            $scope.pointImagePath = "/bundles/galaxyfrontend/" + data.image;
            $scope.pointImage = true;
        }
    }

    $scope.getUser();
    $scope.jumpTooltip = false;
    $scope.jumpTooltipText = "empty";
    $scope.distance = function() {
        if (!$scope.user) {
            return 0;
        }
        if (!$scope.checkCoords()) {
            return 0;
        }
        var funds;
        if ($scope.user.gameInfo.flipper.paymentFromDeposit) {
            funds = $scope.user.fundsInfo.deposite;
        } else {
            funds = $scope.user.fundsInfo.active;
        }
        if (funds < $scope.user.gameInfo.flipper.costJump) {
            $scope.jumpTooltipText = 'Соберай бабло!'; //TODO
            $scope.jumpTooltip = true;
            return 0;
        }
        var dx = $scope.user.gameInfo.x - $scope.x;
        var dy = $scope.user.gameInfo.y - $scope.y;
        var dz = $scope.user.gameInfo.z - $scope.z;
        var dist1 = Math.sqrt(Math.pow(dx, 2) + Math.pow(dy, 2) + Math.pow(dz, 2));
        var dist2 = Math.sqrt(Math.pow(999 - Math.abs(dx), 2) + Math.pow(dy, 2) + Math.pow(dz, 2));
        var dist3 = Math.sqrt(Math.pow(dx, 2) + Math.pow(999 - Math.abs(dy), 2) + Math.pow(dz, 2));
        var dist4 = Math.sqrt(Math.pow(dx, 2) + Math.pow(dy, 2) + Math.pow(999 - Math.abs(dz), 2));
        var dist5 = Math.sqrt(Math.pow(999 - Math.abs(dx), 2) + Math.pow(999 - Math.abs(dy), 2) + Math.pow(dz, 2));
        var dist6 = Math.sqrt(Math.pow(999 - Math.abs(dx), 2) + Math.pow(dy, 2) + Math.pow(999 - Math.abs(dz), 2));
        var dist7 = Math.sqrt(Math.pow(dx, 2) + Math.pow(999 - Math.abs(dy), 2) + Math.pow(999 - Math.abs(dz), 2));
        var dist8 = Math.sqrt(Math.pow(999 - Math.abs(dx), 2) + Math.pow(999 - Math.abs(dy), 2) + Math.pow(999 - Math.abs(dz), 2));
        var dist = Math.min(dist1, dist2, dist3, dist4, dist5, dist6, dist7, dist8);
        if (dist > $scope.user.gameInfo.flipper.maxJump) {
            var message = 'Превышена допустимая дальность прыжка.';
            var superjumps = $scope.user.gameInfo.superJumps;
            if ($scope.user.gameInfo.superJumps > 0) {
                message += " Суперпрыжков в наличии: " + superjumps + ".";
            }
            $scope.jumpTooltipText = message; //TODO
            $scope.jumpTooltip = true;
        } else {
            $scope.jumpTooltip = false;
        }
        if (dist - Math.floor(dist) == 0) {
            return dist;
        }
        return dist.toFixed(2);
    }
    $scope.checkCoords = function() {
        var success = true;
        if (isNaN(parseInt($scope.x)) || $scope.x == 0
                || isNaN(parseInt($scope.y)) || $scope.y == 0
                || isNaN(parseInt($scope.z)) || $scope.z == 0) {
            success = false;
            $scope.jumpTooltipText = 'Недопустимые коорднаты'; //TODO
            $scope.jumpTooltip = true;
        } else {
            $scope.jumpTooltip = false;
        }
        return success;
    }
    $scope.increment = function(varName) {
        var coord = $scope[varName];
        coord++;
        if (coord <= $scope.pointMax) {
            $scope[varName] = coord;
        }
    }
    $scope.decrement = function(varName) {
        var coord = $scope[varName];
        coord--;
        if (coord >= $scope.pointMin) {
            $scope[varName] = coord;
        }
    }
    $scope.coordChange = function(old, varName) {
        var coord = $scope[varName];
        if (coord > 1000 || coord < 1) {
            $scope[varName] = parseInt(old);
        }
    }
    $scope.toDeposite = function(value, max) {
        console.log(value);
        console.log(max);
        if (parseInt(value) > parseInt(max)) {
            $scope.cost = parseInt(max);
        }
    }



    $scope.pointMin = 1;
    $scope.pointMax = 1000;
    $scope.jump = function() {
        var dist = $scope.distance();
        var gameInfo = $scope.user.gameInfo;
        var superjumps = gameInfo.superJumps;
        var superjump = false;
        if (dist > gameInfo.flipper.maxJump && gameInfo.flipper.rentDuration > 0) {
            if (superjumps == 0) {
                return;
            } else {
                superjump = true;
            }
        }
        $scope.jumpActive = true;
        var data = {
            x: parseInt($scope.x),
            y: parseInt($scope.y),
            z: parseInt($scope.z),
            superjump: superjump
        };


        $http.post('/jump', data).success($scope.jumpCallback);
    }

    $scope.semiAutopilot = function() {
        var x = $scope.user.gameInfo.x;
        var y = $scope.user.gameInfo.y;
        var z = $scope.user.gameInfo.z;
        if ($scope.user.gameInfo.minRadius != 0 && $scope.user.gameInfo.maxRadius != 0) {
            minR = $scope.user.gameInfo.minRadius;
            maxR = $scope.user.gameInfo.maxRadius;
        } else {
            minR = $scope.user.gameInfo.flipper.nextPointDistance;
            maxR = $scope.user.gameInfo.flipper.maxJump;
        }

        var xn, yn, zn;
        xn = parseInt(Math.random() * (maxR * 2 + 1) - maxR);
        if (xn == maxR) {
            yn = 0;
            zn = 0;
        } else {
            maxY = Math.pow(Math.pow(maxR, 2) - Math.pow(xn, 2), (1 / 2));
            yn = parseInt(Math.random() * maxY * 2 + 1 - maxY);
            ebu = Math.pow(maxR, 2) - (Math.pow(xn, 2) + Math.pow(yn, 2));
            if (ebu == 0) {
                zn = 0
            } else {
                maxZ = Math.pow(ebu, (1 / 2));
                zn = parseInt(Math.random() * maxZ * 2 - 1 - maxZ);
                minR2 = Math.pow(minR, 2);
                count = 0;
                while (minR2 >= (Math.pow(zn, 2) + Math.pow(yn, 2) + Math.pow(xn, 2))) {
                    zn = Math.random() * maxZ - 1;
                    count++;
                    if (count > 1000) {
                        break;
                    }
                }
            }
        }
        len = Math.pow(Math.pow(xn, 2) + Math.pow(yn, 2) + Math.pow(zn, 2), (1 / 2));
        console.log("MaxR " + maxR);
        console.log("len " + len);
        console.log("xn=" + xn + "|" + "yn=" + yn + "|" + "zn=" + zn);
        xd = x + xn;
        yd = y + yn;
        zd = z + zn;

        if (xd > 1000) {
            xd = xd - 1000
        } else if (xd <= 0) {
            xd = 999 + xd
        }

        if (yd > 1000) {
            yd = yd - 1000
        } else if (yd <= 0) {
            yd = 999 + yd
        }

        if (zd > 1000) {
            zd = zd - 1000
        } else if (zd <= 0) {
            zd = 999 + zd
        }
        console.log("xn=" + xd + "|" + "yn=" + yd + "|" + "zn=" + zd);
        var data = {
            x: parseInt(xd),
            y: parseInt(yd),
            z: parseInt(zd),
            superjump: false
        };
        $http.post('/jump', data).success($scope.jumpCallback);
    }

    $scope.radar = function() {
        var data = {
            pointType: $scope.pointType
        };
        $http.post('/flipper/radar', data).success($scope.radarCallback);
    }
    $scope.radarCallback = function(data) {
        console.log(data.radar);
        if (data.result == 'success') {
            if (data.user.gameInfo.flipper.id == 2)
            {
                $scope.zoneShow = true;
            } else {
                $scope.zoneShow2 = true;
            }
            $scope.updateUserInfo(data.user);
        } else {
            alert("FAIL");
        }
    }

    $scope.buyFlipper = function(id) {
        var data = {
            flipperId: parseInt(id)
        };
        $http.post('/store/buy_flipper', data).success($scope.flipperCallback);
    }
    $scope.flipperCallback = function(data) {
        console.log(data);
        if (data.result == 'success') {
            $scope.updateUserInfo(data.user);
        }
    }

    $scope.buyZone = function(value) {
        if ($scope.zoneShow || $scope.zoneShow2) {
            var data = {
                value: parseInt(value)
            };
            $http.post('/store/buy_zone', data).success($scope.zoneCallback);
        }
    }
    $scope.zoneCallback = function(data) {
        console.log(data);
        if (data.result == 'success') {
            alert("Success zone");
            $scope.zoneShow = false;
            $scope.zoneShow2 = false;
            $scope.updateUserInfo(data.user);
        } else {
            alert("FAIL");
        }
    }

    $scope.deleteZone = function() {
        $http.get('/flipper/delete_zone').success($scope.zoneCallback);
    }


    $scope.activeToSafe = function() {
        var data = {
            value: parseInt($scope.costActive),
            from: 1,
            to: 5
        };
        if (parseInt($scope.costActive) > 0) {
            $http.post('/store/transfer_funds', data).success($scope.transferCallback);
        }
    }
    $scope.safeToActive = function() {
        var data = {
            value: parseInt($scope.costSafe),
            from: 2,
            to: 4
        };
        if (parseInt($scope.costSafe) > 0) {
            $http.post('/store/transfer_funds', data).success($scope.transferCallback);
        }

    }
    $scope.depositeToActive = function() {
        var data = {
            value: parseInt($scope.costDeposite),
            from: 3,
            to: 1
        };
        $http.post('/store/transfer_funds', data).success($scope.transferCallback);
    }

    $scope.buyMessageCount = function() {
        $http.post('/store/buy_message').success($scope.transferCallback);
    }

    $scope.transferCallback = function(data) {
        console.log(data);
        if (data.result == 'success') {
            $scope.updateUserInfo(data.user);
        }
    }
    $scope.jumpCallback = function(data, status) {
        if (data.result == 'success') {
            $scope.jumpActive = false;
            $scope.updateUserInfo(data.user);
            $scope.updatePointImage(data);
            if (data.tag == "black") {
                $scope.logout();
            }
        }
    }

    $scope.logout = function() {
        if (!$scope.logoutAlerted) {
            $timeout(function() {
                location.reload()
            }, 10000);
            $scope.logoutAlerted = true;
        }
    }

    $scope.buyElement = function() {
        $http.get('/buyElement').success($scope.buyCallback);
    }

    $scope.buyCallback = function(data, status) {
        if (data.result == 'success') {
            if (data.prize)
                alert(data.prize);
            $scope.updateUserInfo(data.user);
        }
    };
    $scope.answer = function() {
        if (!$scope.rightAnswer) {
            return;
        }
        var url = '/answer/' + $scope.question.id + "/" + $scope.rightAnswer;
        $http.get(url).success($scope.answerResult);
    }
    $scope.answerResult = function(data, status) {
        $scope.getUser();
    }
}

galaxy.directive('leftTooltip', function() {
    return {
        restrict: 'A',
        link: function($scope, $element, $attrs)
        {
            $element.bind('mouseenter', function(e) {
                var left = $element[0].clientWidth + $element[0].offsetLeft + 120;
                var top = $element[0].offsetTop - 26;
                $scope.tooltipMessage = $attrs.leftTooltip;
                $scope.tooltipStyle = {
                    'top': top + 'px',
                    'left': left + "px",
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
galaxy.directive('integerInput', function() {
    return {
        link: function($scope, $element, $attrs)
        {
            $element.bind('keydown', function(event) {
                if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 ||
                        // Allow: Ctrl+A
                                (event.keyCode == 65 && event.ctrlKey === true) ||
                                // Allow: home, end, left, right
                                        (event.keyCode >= 35 && event.keyCode <= 39)) {
                            // let it happen, don't do anything
                            return;
                        }
                        else {
                            // Ensure that it is a number and stop the keypress
                            if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105) && event.keyCode == 190) {
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
        for (var i = 1; i <= total; i++)
            input.push(i);
        return input;
    };
});
