<footer class="bg-[#1a1a2e]/80 backdrop-blur-lg text-purple-200 border-t border-purple-500/20 py-8">
  <div class="container mx-auto px-4 text-center">
      <p class="font-magical mb-2 flex items-center justify-center gap-2">
        <i data-lucide="sparkles" class="w-4 h-4 text-purple-400"></i>
        Crafted with ancient magics
        <i data-lucide="sparkles" class="w-4 h-4 text-purple-400"></i>
      </p>
      <p class="font-magical text-purple-400/80">
        <?php $name = $_SESSION['username'] ?? ''; echo footer_forger($name) ?>
      </p>
      <p class="font-magical text-purple-400/80">
        &copy; <span id="year"><?php echo date("Y"); ?></span> Arcane Chronicles
      </p>
    </div>
  </footer>