<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\ChildController;
use App\Controllers\CepController;
use App\Controllers\DashboardController;
use App\Controllers\DeliveryEventController;
use App\Controllers\EquipmentController;
use App\Controllers\EquipmentLoanController;
use App\Controllers\FamilyController;
use App\Controllers\PersonController;
use App\Controllers\ReportController;
use App\Controllers\UserController;
use App\Controllers\VisitController;
use App\Core\Container;
use App\Core\Router;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\PermissionMiddleware;

return static function (Router $router, Container $container): void {
    $authOnly = static function (callable $action): void {
        (new AuthMiddleware())->handle($action);
    };

    $adminOnly = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('users.manage', $action);
        });
    };

    $familyView = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('families.view', $action);
        });
    };

    $familyManage = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('families.manage', $action);
        });
    };

    $childView = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('children.view', $action);
        });
    };

    $childManage = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('children.manage', $action);
        });
    };

    $personView = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('people.view', $action);
        });
    };

    $personManage = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('people.manage', $action);
        });
    };

    $deliveryView = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('deliveries.view', $action);
        });
    };

    $deliveryManage = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('deliveries.manage', $action);
        });
    };

    $equipmentView = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('equipment.view', $action);
        });
    };

    $equipmentManage = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('equipment.manage', $action);
        });
    };

    $visitView = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('visits.view', $action);
        });
    };

    $visitManage = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('visits.manage', $action);
        });
    };

    $reportView = static function (callable $action): void {
        (new AuthMiddleware())->handle(static function () use ($action): void {
            (new PermissionMiddleware())->handle('reports.view', $action);
        });
    };

    $router->get('/', static function () use ($container): void {
        if (\App\Core\Session::has('auth_user')) {
            \App\Core\Response::redirect('/dashboard');
        }

        \App\Core\Response::redirect('/login');
    });

    $router->get('/health', static function (): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'ok',
            'app' => 'dashboard_php_pbt',
            'time' => date('c'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    });

    $router->get('/api/cep', static function () use ($container, $authOnly): void {
        $authOnly(static function () use ($container): void {
            (new CepController($container))->lookup();
        });
    });

    $router->get('/login', static function () use ($container): void {
        (new AuthController($container))->showLogin();
    });

    $router->post('/login', static function () use ($container): void {
        (new AuthController($container))->login();
    });

    $router->get('/forgot-password', static function () use ($container): void {
        (new AuthController($container))->showForgotPassword();
    });

    $router->post('/forgot-password', static function () use ($container): void {
        (new AuthController($container))->requestPasswordReset();
    });

    $router->get('/reset-password', static function () use ($container): void {
        (new AuthController($container))->showResetPassword();
    });

    $router->post('/reset-password', static function () use ($container): void {
        (new AuthController($container))->resetPassword();
    });

    $router->post('/logout', static function () use ($container): void {
        (new AuthController($container))->logout();
    });

    $router->get('/logout', static function () use ($container): void {
        (new AuthController($container))->logout();
    });

    $router->get('/dashboard', static function () use ($container, $authOnly): void {
        $authOnly(static function () use ($container): void {
            (new DashboardController($container))->index();
        });
    });

    $router->get('/families', static function () use ($container, $familyView): void {
        $familyView(static function () use ($container): void {
            (new FamilyController($container))->index();
        });
    });

    $router->get('/families/show', static function () use ($container, $familyView): void {
        $familyView(static function () use ($container): void {
            (new FamilyController($container))->show();
        });
    });

    $router->get('/families/create', static function () use ($container, $familyManage): void {
        $familyManage(static function () use ($container): void {
            (new FamilyController($container))->create();
        });
    });

    $router->post('/families', static function () use ($container, $familyManage): void {
        $familyManage(static function () use ($container): void {
            (new FamilyController($container))->store();
        });
    });

    $router->get('/families/edit', static function () use ($container, $familyManage): void {
        $familyManage(static function () use ($container): void {
            (new FamilyController($container))->edit();
        });
    });

    $router->post('/families/update', static function () use ($container, $familyManage): void {
        $familyManage(static function () use ($container): void {
            (new FamilyController($container))->update();
        });
    });

    $router->post('/families/members', static function () use ($container, $familyManage): void {
        $familyManage(static function () use ($container): void {
            (new FamilyController($container))->storeMember();
        });
    });

    $router->post('/families/members/update', static function () use ($container, $familyManage): void {
        $familyManage(static function () use ($container): void {
            (new FamilyController($container))->updateMember();
        });
    });

    $router->post('/families/members/delete', static function () use ($container, $familyManage): void {
        $familyManage(static function () use ($container): void {
            (new FamilyController($container))->deleteMember();
        });
    });

    $router->get('/children', static function () use ($container, $childView): void {
        $childView(static function () use ($container): void {
            (new ChildController($container))->index();
        });
    });

    $router->get('/children/create', static function () use ($container, $childManage): void {
        $childManage(static function () use ($container): void {
            (new ChildController($container))->create();
        });
    });

    $router->post('/children', static function () use ($container, $childManage): void {
        $childManage(static function () use ($container): void {
            (new ChildController($container))->store();
        });
    });

    $router->get('/children/edit', static function () use ($container, $childManage): void {
        $childManage(static function () use ($container): void {
            (new ChildController($container))->edit();
        });
    });

    $router->post('/children/update', static function () use ($container, $childManage): void {
        $childManage(static function () use ($container): void {
            (new ChildController($container))->update();
        });
    });

    $router->post('/children/delete', static function () use ($container, $childManage): void {
        $childManage(static function () use ($container): void {
            (new ChildController($container))->delete();
        });
    });

    $router->get('/people', static function () use ($container, $personView): void {
        $personView(static function () use ($container): void {
            (new PersonController($container))->index();
        });
    });

    $router->get('/people/show', static function () use ($container, $personView): void {
        $personView(static function () use ($container): void {
            (new PersonController($container))->show();
        });
    });

    $router->get('/people/create', static function () use ($container, $personManage): void {
        $personManage(static function () use ($container): void {
            (new PersonController($container))->create();
        });
    });

    $router->post('/people', static function () use ($container, $personManage): void {
        $personManage(static function () use ($container): void {
            (new PersonController($container))->store();
        });
    });

    $router->get('/people/edit', static function () use ($container, $personManage): void {
        $personManage(static function () use ($container): void {
            (new PersonController($container))->edit();
        });
    });

    $router->post('/people/update', static function () use ($container, $personManage): void {
        $personManage(static function () use ($container): void {
            (new PersonController($container))->update();
        });
    });

    $router->post('/people/social-records', static function () use ($container, $personManage): void {
        $personManage(static function () use ($container): void {
            (new PersonController($container))->storeSocialRecord();
        });
    });

    $router->post('/people/referrals', static function () use ($container, $personManage): void {
        $personManage(static function () use ($container): void {
            (new PersonController($container))->storeReferral();
        });
    });

    $router->post('/people/referrals/update', static function () use ($container, $personManage): void {
        $personManage(static function () use ($container): void {
            (new PersonController($container))->updateReferral();
        });
    });

    $router->post('/people/referrals/delete', static function () use ($container, $personManage): void {
        $personManage(static function () use ($container): void {
            (new PersonController($container))->deleteReferral();
        });
    });

    $router->post('/people/spiritual-followups', static function () use ($container, $personManage): void {
        $personManage(static function () use ($container): void {
            (new PersonController($container))->storeSpiritualFollowup();
        });
    });

    $router->post('/people/spiritual-followups/update', static function () use ($container, $personManage): void {
        $personManage(static function () use ($container): void {
            (new PersonController($container))->updateSpiritualFollowup();
        });
    });

    $router->post('/people/spiritual-followups/delete', static function () use ($container, $personManage): void {
        $personManage(static function () use ($container): void {
            (new PersonController($container))->deleteSpiritualFollowup();
        });
    });

    $router->get('/delivery-events', static function () use ($container, $deliveryView): void {
        $deliveryView(static function () use ($container): void {
            (new DeliveryEventController($container))->index();
        });
    });

    $router->get('/delivery-events/show', static function () use ($container, $deliveryView): void {
        $deliveryView(static function () use ($container): void {
            (new DeliveryEventController($container))->show();
        });
    });

    $router->get('/delivery-events/create', static function () use ($container, $deliveryManage): void {
        $deliveryManage(static function () use ($container): void {
            (new DeliveryEventController($container))->create();
        });
    });

    $router->post('/delivery-events', static function () use ($container, $deliveryManage): void {
        $deliveryManage(static function () use ($container): void {
            (new DeliveryEventController($container))->store();
        });
    });

    $router->get('/delivery-events/edit', static function () use ($container, $deliveryManage): void {
        $deliveryManage(static function () use ($container): void {
            (new DeliveryEventController($container))->edit();
        });
    });

    $router->post('/delivery-events/update', static function () use ($container, $deliveryManage): void {
        $deliveryManage(static function () use ($container): void {
            (new DeliveryEventController($container))->update();
        });
    });

    $router->post('/delivery-events/deliveries', static function () use ($container, $deliveryManage): void {
        $deliveryManage(static function () use ($container): void {
            (new DeliveryEventController($container))->storeDelivery();
        });
    });

    $router->post('/delivery-events/deliveries/auto', static function () use ($container, $deliveryManage): void {
        $deliveryManage(static function () use ($container): void {
            (new DeliveryEventController($container))->autoGenerateDeliveries();
        });
    });

    $router->get('/delivery-events/deliveries/csv', static function () use ($container, $deliveryView): void {
        $deliveryView(static function () use ($container): void {
            (new DeliveryEventController($container))->exportEventCsv();
        });
    });

    $router->get('/delivery-events/print', static function () use ($container, $deliveryView): void {
        $deliveryView(static function () use ($container): void {
            (new DeliveryEventController($container))->printList();
        });
    });

    $router->post('/delivery-events/deliveries/status', static function () use ($container, $deliveryManage): void {
        $deliveryManage(static function () use ($container): void {
            (new DeliveryEventController($container))->updateDeliveryStatus();
        });
    });

    $router->post('/delivery-events/close', static function () use ($container, $deliveryManage): void {
        $deliveryManage(static function () use ($container): void {
            (new DeliveryEventController($container))->closeEvent();
        });
    });

    $router->post('/delivery-events/reopen', static function () use ($container, $deliveryManage): void {
        $deliveryManage(static function () use ($container): void {
            (new DeliveryEventController($container))->reopenEvent();
        });
    });

    $router->get('/equipment', static function () use ($container, $equipmentView): void {
        $equipmentView(static function () use ($container): void {
            (new EquipmentController($container))->index();
        });
    });

    $router->get('/equipment/create', static function () use ($container, $equipmentManage): void {
        $equipmentManage(static function () use ($container): void {
            (new EquipmentController($container))->create();
        });
    });

    $router->post('/equipment', static function () use ($container, $equipmentManage): void {
        $equipmentManage(static function () use ($container): void {
            (new EquipmentController($container))->store();
        });
    });

    $router->get('/equipment/edit', static function () use ($container, $equipmentManage): void {
        $equipmentManage(static function () use ($container): void {
            (new EquipmentController($container))->edit();
        });
    });

    $router->post('/equipment/update', static function () use ($container, $equipmentManage): void {
        $equipmentManage(static function () use ($container): void {
            (new EquipmentController($container))->update();
        });
    });

    $router->post('/equipment/delete', static function () use ($container, $equipmentManage): void {
        $equipmentManage(static function () use ($container): void {
            (new EquipmentController($container))->delete();
        });
    });

    $router->get('/equipment-loans', static function () use ($container, $equipmentView): void {
        $equipmentView(static function () use ($container): void {
            (new EquipmentLoanController($container))->index();
        });
    });

    $router->post('/equipment-loans', static function () use ($container, $equipmentManage): void {
        $equipmentManage(static function () use ($container): void {
            (new EquipmentLoanController($container))->store();
        });
    });

    $router->post('/equipment-loans/return', static function () use ($container, $equipmentManage): void {
        $equipmentManage(static function () use ($container): void {
            (new EquipmentLoanController($container))->returnLoan();
        });
    });

    $router->get('/visits', static function () use ($container, $visitView): void {
        $visitView(static function () use ($container): void {
            (new VisitController($container))->index();
        });
    });

    $router->get('/visits/create', static function () use ($container, $visitManage): void {
        $visitManage(static function () use ($container): void {
            (new VisitController($container))->create();
        });
    });

    $router->post('/visits', static function () use ($container, $visitManage): void {
        $visitManage(static function () use ($container): void {
            (new VisitController($container))->store();
        });
    });

    $router->get('/visits/edit', static function () use ($container, $visitManage): void {
        $visitManage(static function () use ($container): void {
            (new VisitController($container))->edit();
        });
    });

    $router->post('/visits/update', static function () use ($container, $visitManage): void {
        $visitManage(static function () use ($container): void {
            (new VisitController($container))->update();
        });
    });

    $router->post('/visits/conclude', static function () use ($container, $visitManage): void {
        $visitManage(static function () use ($container): void {
            (new VisitController($container))->conclude();
        });
    });

    $router->post('/visits/delete', static function () use ($container, $visitManage): void {
        $visitManage(static function () use ($container): void {
            (new VisitController($container))->delete();
        });
    });

    $router->get('/reports', static function () use ($container, $reportView): void {
        $reportView(static function () use ($container): void {
            (new ReportController($container))->index();
        });
    });

    $router->get('/reports/pdf', static function () use ($container, $reportView): void {
        $reportView(static function () use ($container): void {
            (new ReportController($container))->exportPdf();
        });
    });

    $router->get('/reports/csv', static function () use ($container, $reportView): void {
        $reportView(static function () use ($container): void {
            (new ReportController($container))->exportCsv();
        });
    });

    $router->get('/reports/excel', static function () use ($container, $reportView): void {
        $reportView(static function () use ($container): void {
            (new ReportController($container))->exportExcel();
        });
    });

    $router->get('/reports/xlsx', static function () use ($container, $reportView): void {
        $reportView(static function () use ($container): void {
            (new ReportController($container))->exportExcel();
        });
    });

    $router->get('/users', static function () use ($container, $adminOnly): void {
        $adminOnly(static function () use ($container): void {
            (new UserController($container))->index();
        });
    });

    $router->get('/users/create', static function () use ($container, $adminOnly): void {
        $adminOnly(static function () use ($container): void {
            (new UserController($container))->create();
        });
    });

    $router->post('/users', static function () use ($container, $adminOnly): void {
        $adminOnly(static function () use ($container): void {
            (new UserController($container))->store();
        });
    });

    $router->get('/users/edit', static function () use ($container, $adminOnly): void {
        $adminOnly(static function () use ($container): void {
            (new UserController($container))->edit();
        });
    });

    $router->post('/users/update', static function () use ($container, $adminOnly): void {
        $adminOnly(static function () use ($container): void {
            (new UserController($container))->update();
        });
    });

    $router->post('/users/toggle', static function () use ($container, $adminOnly): void {
        $adminOnly(static function () use ($container): void {
            (new UserController($container))->toggleActive();
        });
    });
};
