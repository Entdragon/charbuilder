
    </main>
  </div><!-- #uj-body -->
</div><!-- #uj-wrapper -->

<script>
(function () {
  // Live name-filter for any .cards-grid or .filterable-table on list pages
  const searchInput = document.getElementById('uj-live-search');
  if (!searchInput) return;

  const target = searchInput.dataset.target || '.card';
  const items  = () => document.querySelectorAll(target);

  searchInput.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    items().forEach(el => {
      const name = (el.dataset.name || el.textContent).toLowerCase();
      el.classList.toggle('hidden', q.length > 0 && !name.includes(q));
    });
  });
}());
</script>
</body>
</html>
