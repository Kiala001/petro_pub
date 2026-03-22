<!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sb-head">
      <div><div class="sb-logo">PETRO<span>PUB</span></div><div class="sb-role">Portal do Estudante</div></div>
      <button class="sb-tog" id="sb-close" onclick="closeSB()">✕</button>
      <button class="sb-tog" id="sb-col"   onclick="toggleCol()">◀</button>
    </div>
    <div class="sb-user">
      <div class="ava ava-cr"><?php echo $userInitials; ?></div>
      <div class="sb-ui"><div class="sb-un"><?php echo $userName; ?></div><div class="sb-ue"><?php echo $userEmail; ?></div></div>
    </div>
    <!-- ... navegação ... -->
     <div class="nav-s">
        <div class="nav-l">Biblioteca</div>
        <a href="dashboard.php" class="nav-i" data-tip="Início"><span class="ni"><i class="fa fa-home"></i></span><span class="nt">Início</span></a>
        <a href="library.php" class="nav-i" data-tip="Documentos"><span class="ni"><i class="fa fa-book"></i></span><span class="nt">Biblioteca</span></a>
        <a href="my-documents.php" class="nav-i" data-tip="Os Meus"><span class="ni"><i class="fa fa-folder"></i></span><span class="nt">Os Meus Documentos</span></a>
    </div>
    <div class="nav-s">
        <div class="nav-l">Conta</div>
        <a href="pair-review.php" class="nav-i" data-tip="Avaliações"><span class="ni"><i class="fa fa-comment-o"></i></span><span class="nt">Avaliação por pares</span><span class="nb">-</span></a>
        <a href="gamification.php" class="nav-i act" data-tip="Pontos"><span class="ni"><i class="fa fa-star"></i></span><span class="nt">Pontos & Ranking</span><span class="ng">-</span></a>
        <a href="notification.php" class="nav-i" data-tip="Notificações"><span class="ni">🔔</span><span class="nt">Notificações</span><span class="nb">3</span></a>
    </div>
    <div class="sb-foot">
        <a href="logout.php" class="nav-i" data-tip="Sair"><span class="ni">🚪</span><span class="nt">Sair da Sessão</span></a>
    </div>
  </aside>    
