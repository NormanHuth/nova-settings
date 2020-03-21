Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'nova-valuestore',
      path: '/nova-valuestore',
      component: require('./views/Settings').default,
    },
  ]);
});
