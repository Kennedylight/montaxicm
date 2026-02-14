window.addEventListener("load", function () {
  var routes = {
    "/": {
      on: function () {
        router.setRoute("/main");
      },
    },

    "/new": {
      on: function () {
        resetAll();
        goAdd();
      },
    },

    "/main": {
      on: function () {
        goOn(0);
      },
    },

    "/stats": {
      on: function () {
        goOn(1)
      },
    },
    "/stats/:id": {
      on: function (id) {
        goOn(1, id)
      },
    },

    "/commands": {
      on: function () {
        goOn(2)
      },
    },

    "/stocks": {
      on: function () {
        goOn(3, 1)
      },
    },
    "/stocks/page/:id": {
      on: function (id) {
        const pageNumber = parseInt(id);
        if (isNaN(pageNumber) || pageNumber < 1) {
          router.setRoute('/stocks');
          return;
        }
        goOn(3, pageNumber)
      },
    },

    "/clientsManager": {
      on: function () {
        goOn(4)
      },
    },
    "/clientsManager/:id": {
      on: function () {
        goOn(4, id)
      },
    },

    "/usersManager": {
      on: function () {
        goOn(5)
      },
    },
    "/usersManager/:id": {
      on: function (id) {
        goOn(5, id)
      },
    },

    "/rated": {
      on: function () {
        goOn(6)
      },
    },

    "/remboursement": {
      on: function () {
        goOn(7)
      },
    },

    "/settings": {
      on: function () {
        goOn(8)
        switchTab('user')
      },
    },
    "/settings/website": {
      on: function () {
        goOn(8)
        switchTab('website')
      },
    },
    "/settings/:id": {
      on: function (id) {
        goOn(8)
        switchTab('user', id)
      },
    },
    "/settings/website/:id": {
      on: function (id) {
        goOn(8)
        switchTab('website', id)
      },
    },

    "/n": {
      on: function () {
        openNotifications()
      }
    },
    "/n/:id": {
      on: function (id) {
        openNotifications(id)
      },
    },

    "/new/product": {
      on: function () {
        goOn(9)
      },
    },
    "/GestCategoryProduct": {
      on: function () {
        goOn(9, 1, 1)
      },
    },
    "/GestCategoryProduct/:id": {
      on: function (id) {
        goOn(9, id, 1)
      },
    },
    "/GestBanner": {
      on: function () {
        goOn(10, 1, 1)
      },
    },
    
    "/new/user": {
      on: function () {
        if (authsUser.includes(2)) {
          loadPermissions(true)
        }
      },
    },
    
    "/systemLogs": {
      on: function () {
        openLogsModal()
      },
    },
    
    "/newsletter": {
      on: function () {
        openNewsletterModal()
      },
    },
  };
  var router = Router(routes);
  router.init();

});