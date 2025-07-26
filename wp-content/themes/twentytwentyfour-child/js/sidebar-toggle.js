//console.log('🔔 sidebar-toggle.js loaded');
//
//
//const sidebar = document.getElementById('site-sidebar');
//const toggle = document.getElementById('menu-toggle');
//const main = document.getElementById('main-content');
//
//let collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
//if (collapsed) {
//  sidebar.classList.add('collapsed');
//  main.style.marginLeft = '60px';
//}
//
//if (window.innerWidth <= 768 && !collapsed) {
//  sidebar.classList.add('collapsed');
//  main.style.marginLeft = '60px';
//  collapsed = true;
//  localStorage.setItem('sidebarCollapsed', true);
//}
//
//toggle.addEventListener('click', () => {
//  sidebar.classList.toggle('collapsed');
//  const isCollapsed = sidebar.classList.contains('collapsed');
//  main.style.marginLeft = isCollapsed ? '60px' : '250px';
//  localStorage.setItem('sidebarCollapsed', isCollapsed);
//});
//
//console.log('sidebar-toggle.js loaded');
//
//const sidebar = document.getElementById('site-sidebar');
// …rest of your code…
