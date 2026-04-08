
    </main>
  </div><!-- #ic-body -->
</div><!-- #ic-wrapper -->

<script>
(function () {
  const searchInput = document.getElementById('ic-live-search');
  if (!searchInput) return;
  const target = searchInput.dataset.target || '.card';
  searchInput.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    document.querySelectorAll(target).forEach(el => {
      const text = (el.dataset.name || el.textContent).toLowerCase();
      el.classList.toggle('hidden', q.length > 0 && !text.includes(q));
    });
  });
}());
</script>
</body>
</html>
