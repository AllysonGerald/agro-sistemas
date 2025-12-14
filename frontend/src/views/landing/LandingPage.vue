<template>
  <div class="landing-page">
    <!-- Navbar -->
    <nav class="navbar">
      <div class="container">
        <div class="nav-content">
          <SimpleLogo size="small" />

          <div class="nav-links" :class="{ active: mobileMenuOpen }">
            <a href="#inicio" @click="scrollTo('inicio')">Início</a>
            <a href="#recursos" @click="scrollTo('recursos')">Recursos</a>
            <a href="#funcionalidades" @click="scrollTo('funcionalidades')">Funcionalidades</a>
            <a href="#contato" @click="scrollTo('contato')">Contato</a>
          </div>

          <div class="nav-actions">
            <router-link to="/login" class="btn-login">Entrar</router-link>
            <router-link to="/login" class="btn-start">Começar Grátis</router-link>
          </div>

          <button class="mobile-menu-btn" @click="mobileMenuOpen = !mobileMenuOpen">
            <i :class="mobileMenuOpen ? 'fas fa-times' : 'fas fa-bars'"></i>
          </button>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section id="inicio" class="hero-section">
      <div class="hero-bg"></div>
      <div class="container">
        <div class="hero-grid">
          <div class="hero-content">
            <div class="badge-hero">
              <i class="fas fa-seedling"></i>
              <span>Sistema Completo de Gestão Rural</span>
            </div>
            <h1 class="hero-title">
              Transforme a Gestão do seu <span class="highlight">Agronegócio</span>
            </h1>
            <p class="hero-text">
              Controle completo de rebanhos, produção, finanças e manejo em uma plataforma moderna.
              Tome decisões inteligentes baseadas em dados reais.
            </p>
            <div class="hero-buttons">
              <router-link to="/login" class="btn-primary">
                <i class="fas fa-rocket"></i>
                Começar Agora - Grátis
              </router-link>
              <button class="btn-secondary" @click="scrollTo('recursos')">
                <i class="fas fa-play-circle"></i>
                Ver Demonstração
              </button>
            </div>
            <div class="hero-stats">
              <div class="stat">
                <strong>500+</strong>
                <span>Produtores</span>
              </div>
              <div class="stat">
                <strong>50k+</strong>
                <span>Animais</span>
              </div>
              <div class="stat">
                <strong>+35%</strong>
                <span>Produtividade</span>
              </div>
            </div>
          </div>
          <div class="hero-visual">
            <div class="dashboard-card">
              <div class="card-header">
                <span></span>
                <span></span>
                <span></span>
              </div>
              <div class="card-content">
                <div class="card-stats">
                  <div class="card-stat green">
                    <i class="fas fa-cow"></i>
                    <div>
                      <small>Total de Animais</small>
                      <strong>1.250</strong>
                    </div>
                  </div>
                  <div class="card-stat blue">
                    <i class="fas fa-dollar-sign"></i>
                    <div>
                      <small>Receita Mensal</small>
                      <strong>R$ 450k</strong>
                    </div>
                  </div>
                </div>
                <div class="card-chart">
                  <div class="chart-bar" style="height: 40%"></div>
                  <div class="chart-bar" style="height: 70%"></div>
                  <div class="chart-bar" style="height: 55%"></div>
                  <div class="chart-bar" style="height: 85%"></div>
                  <div class="chart-bar" style="height: 60%"></div>
                  <div class="chart-bar" style="height: 90%"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Recursos Section -->
    <section id="recursos" class="recursos-section">
      <div class="container">
        <div class="section-title">
          <div class="title-badge">
            <i class="fas fa-gem"></i>
            <span>Recursos Premium</span>
          </div>
          <h2>Por que Escolher o AgroSistemas?</h2>
          <p>Ferramentas profissionais para gestão completa da sua propriedade rural</p>
        </div>

        <div class="recursos-grid">
          <div class="recurso-card">
            <div class="recurso-icon green">
              <i class="fas fa-chart-line"></i>
            </div>
            <h3>Dashboard em Tempo Real</h3>
            <p>Acompanhe todas as métricas importantes com gráficos interativos e alertas inteligentes.</p>
          </div>

          <div class="recurso-card">
            <div class="recurso-icon blue">
              <i class="fas fa-cow"></i>
            </div>
            <h3>Gestão de Rebanho</h3>
            <p>Controle completo de animais, lotes, pesagens, vacinas e genealogia detalhada.</p>
          </div>

          <div class="recurso-card">
            <div class="recurso-icon yellow">
              <i class="fas fa-dollar-sign"></i>
            </div>
            <h3>Controle Financeiro</h3>
            <p>Gerencie receitas, despesas e tenha visão completa da saúde financeira.</p>
          </div>

          <div class="recurso-card">
            <div class="recurso-icon red">
              <i class="fas fa-file-pdf"></i>
            </div>
            <h3>Relatórios Profissionais</h3>
            <p>Exporte dados em PDF, Excel e CSV com design profissional e dados detalhados.</p>
          </div>

          <div class="recurso-card">
            <div class="recurso-icon purple">
              <i class="fas fa-calculator"></i>
            </div>
            <h3>Calculadora Pecuária</h3>
            <p>Ferramentas para GPD, conversão alimentar, custos e projeção de lucros.</p>
          </div>

          <div class="recurso-card">
            <div class="recurso-icon cyan">
              <i class="fas fa-cloud"></i>
            </div>
            <h3>100% na Nuvem</h3>
            <p>Acesse de qualquer lugar com backup automático e segurança total dos dados.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Funcionalidades Section -->
    <section id="funcionalidades" class="funcionalidades-section">
      <div class="container">
        <div class="section-title">
          <div class="title-badge">
            <i class="fas fa-list-check"></i>
            <span>Funcionalidades</span>
          </div>
          <h2>Sistema de Gestão Completo</h2>
          <p>Todas as ferramentas que você precisa em um só lugar</p>
        </div>

        <div class="funcionalidades-grid">
          <div class="func-card">
            <div class="func-header">
              <i class="fas fa-paw"></i>
              <h3>Gestão de Animais</h3>
            </div>
            <ul>
              <li><i class="fas fa-check"></i> Fichas individuais com fotos</li>
              <li><i class="fas fa-check"></i> Histórico de pesagens</li>
              <li><i class="fas fa-check"></i> Controle de vacinas</li>
              <li><i class="fas fa-check"></i> Genealogia completa</li>
            </ul>
          </div>

          <div class="func-card">
            <div class="func-header">
              <i class="fas fa-home"></i>
              <h3>Propriedades</h3>
            </div>
            <ul>
              <li><i class="fas fa-check"></i> Cadastro de propriedades</li>
              <li><i class="fas fa-check"></i> Gestão de unidades</li>
              <li><i class="fas fa-check"></i> Controle de pastos</li>
              <li><i class="fas fa-check"></i> Mapeamento de áreas</li>
            </ul>
          </div>

          <div class="func-card">
            <div class="func-header">
              <i class="fas fa-boxes"></i>
              <h3>Estoque</h3>
            </div>
            <ul>
              <li><i class="fas fa-check"></i> Controle de insumos</li>
              <li><i class="fas fa-check"></i> Alertas de estoque baixo</li>
              <li><i class="fas fa-check"></i> Histórico de movimentações</li>
              <li><i class="fas fa-check"></i> Gestão de fornecedores</li>
            </ul>
          </div>

          <div class="func-card">
            <div class="func-header">
              <i class="fas fa-clipboard-list"></i>
              <h3>Manejo</h3>
            </div>
            <ul>
              <li><i class="fas fa-check"></i> Planejamento de atividades</li>
              <li><i class="fas fa-check"></i> Registro de procedimentos</li>
              <li><i class="fas fa-check"></i> Acompanhamento sanitário</li>
              <li><i class="fas fa-check"></i> Reprodução e cria</li>
            </ul>
          </div>

          <div class="func-card">
            <div class="func-header">
              <i class="fas fa-money-bill-wave"></i>
              <h3>Financeiro</h3>
            </div>
            <ul>
              <li><i class="fas fa-check"></i> Contas a pagar/receber</li>
              <li><i class="fas fa-check"></i> Fluxo de caixa</li>
              <li><i class="fas fa-check"></i> Categorização de despesas</li>
              <li><i class="fas fa-check"></i> Análise de lucratividade</li>
            </ul>
          </div>

          <div class="func-card">
            <div class="func-header">
              <i class="fas fa-file-alt"></i>
              <h3>Relatórios</h3>
            </div>
            <ul>
              <li><i class="fas fa-check"></i> Relatórios em PDF</li>
              <li><i class="fas fa-check"></i> Exportação para Excel</li>
              <li><i class="fas fa-check"></i> Dados em CSV</li>
              <li><i class="fas fa-check"></i> Gráficos interativos</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-content">
          <h2>Pronto para Transformar sua Gestão?</h2>
          <p>Junte-se a centenas de produtores que já aumentaram sua produtividade</p>
          <router-link to="/login" class="btn-cta">
            <i class="fas fa-rocket"></i>
            Começar Grátis Agora
          </router-link>
        </div>
      </div>
    </section>

    <!-- Contato Section -->
    <section id="contato" class="contato-section">
      <div class="container">
        <div class="section-title">
          <div class="title-badge">
            <i class="fas fa-envelope"></i>
            <span>Contato</span>
          </div>
          <h2>Entre em Contato</h2>
          <p>Estamos aqui para ajudar você</p>
        </div>

        <div class="contato-grid">
          <div class="contato-info">
            <div class="info-item">
              <div class="info-icon">
                <i class="fas fa-envelope"></i>
              </div>
              <div class="info-text">
                <h4>Email</h4>
                <p>contato@agrosistemas.com.br</p>
              </div>
            </div>
            <div class="info-item">
              <div class="info-icon">
                <i class="fas fa-phone"></i>
              </div>
              <div class="info-text">
                <h4>Telefone</h4>
                <p>(85) 3333-4444</p>
              </div>
            </div>
            <div class="info-item">
              <div class="info-icon">
                <i class="fas fa-map-marker-alt"></i>
              </div>
              <div class="info-text">
                <h4>Endereço</h4>
                <p>Fortaleza, CE - Brasil</p>
              </div>
            </div>
          </div>

          <form class="contato-form" @submit.prevent="enviarContato">
            <input type="text" placeholder="Seu Nome" v-model="form.nome" required>
            <input type="email" placeholder="Seu Email" v-model="form.email" required>
            <textarea placeholder="Sua Mensagem" v-model="form.mensagem" rows="5" required></textarea>
            <button type="submit" class="btn-form">
              <i class="fas fa-paper-plane"></i>
              Enviar Mensagem
            </button>
          </form>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <div class="footer-grid">
          <div class="footer-about">
            <SimpleLogo size="small" />
            <p>Gestão profissional para o agronegócio. Controle total da sua propriedade rural em uma única plataforma.
            </p>
          </div>
          <div class="footer-col">
            <h4>Links</h4>
            <a href="#inicio" @click="scrollTo('inicio')">Início</a>
            <a href="#recursos" @click="scrollTo('recursos')">Recursos</a>
            <a href="#funcionalidades" @click="scrollTo('funcionalidades')">Funcionalidades</a>
            <a href="#contato" @click="scrollTo('contato')">Contato</a>
          </div>
          <div class="footer-col">
            <h4>Suporte</h4>
            <a href="#">Documentação</a>
            <a href="#">Central de Ajuda</a>
            <a href="#">Termos de Uso</a>
            <a href="#">Privacidade</a>
          </div>
          <div class="footer-col">
            <h4>Redes Sociais</h4>
            <div class="social-links">
              <a href="#"><i class="fab fa-facebook"></i></a>
              <a href="#"><i class="fab fa-instagram"></i></a>
              <a href="#"><i class="fab fa-linkedin"></i></a>
              <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2025 AgroSistemas. Todos os direitos reservados.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import SimpleLogo from '@/components/common/SimpleLogo.vue'

const mobileMenuOpen = ref(false)
const form = ref({
  nome: '',
  email: '',
  mensagem: ''
})

const scrollTo = (id: string) => {
  const element = document.getElementById(id)
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' })
  }
  mobileMenuOpen.value = false
}

const enviarContato = () => {
  alert('Mensagem enviada com sucesso!')
  form.value = { nome: '', email: '', mensagem: '' }
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.landing-page {
  width: 100%;
  overflow-x: hidden;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* Navbar */
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(12px);
  box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
}

.nav-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 0;
}

.nav-links {
  display: flex;
  gap: 2.5rem;
}

.nav-links a {
  text-decoration: none;
  color: #475569;
  font-weight: 600;
  transition: color 0.3s;
  font-size: 0.9375rem;
}

.nav-links a:hover {
  color: #16a34a;
}

.nav-actions {
  display: flex;
  gap: 1rem;
}

.btn-login {
  padding: 0.625rem 1.5rem;
  color: #16a34a;
  text-decoration: none;
  font-weight: 600;
  border: 2px solid #16a34a;
  border-radius: 10px;
  transition: all 0.3s;
  font-size: 0.9375rem;
}

.btn-login:hover {
  background: #16a34a;
  color: white;
}

.btn-start {
  padding: 0.625rem 1.5rem;
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  color: white;
  text-decoration: none;
  font-weight: 600;
  border-radius: 10px;
  transition: all 0.3s;
  box-shadow: 0 4px 16px rgba(22, 163, 74, 0.3);
  font-size: 0.9375rem;
}

.btn-start:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 24px rgba(22, 163, 74, 0.4);
}

.mobile-menu-btn {
  display: none;
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #16a34a;
  cursor: pointer;
}

/* Hero Section */
.hero-section {
  padding: 10rem 0 6rem;
  position: relative;
  overflow: hidden;
}

.hero-bg {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%);
  z-index: -1;
}

.hero-grid {
  display: grid;
  grid-template-columns: 1.1fr 0.9fr;
  gap: 4rem;
  align-items: center;
}

.badge-hero {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1.25rem;
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  color: white;
  border-radius: 50px;
  font-weight: 600;
  font-size: 0.875rem;
  margin-bottom: 2rem;
  box-shadow: 0 4px 16px rgba(22, 163, 74, 0.3);
}

.hero-title {
  font-size: 4rem;
  font-weight: 900;
  line-height: 1.1;
  color: #1e293b;
  margin-bottom: 1.5rem;
}

.highlight {
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-text {
  font-size: 1.125rem;
  color: #64748b;
  line-height: 1.7;
  margin-bottom: 2.5rem;
  max-width: 90%;
}

.hero-buttons {
  display: flex;
  gap: 1rem;
  margin-bottom: 3rem;
}

.btn-primary,
.btn-secondary {
  padding: 1.125rem 2rem;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.625rem;
  transition: all 0.3s;
  border: none;
}

.btn-primary {
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  color: white;
  box-shadow: 0 8px 24px rgba(22, 163, 74, 0.35);
}

.btn-primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 32px rgba(22, 163, 74, 0.45);
}

.btn-secondary {
  background: white;
  color: #16a34a;
  border: 2px solid #16a34a;
}

.btn-secondary:hover {
  background: #16a34a;
  color: white;
}

.hero-stats {
  display: flex;
  gap: 3rem;
}

.stat {
  display: flex;
  flex-direction: column;
}

.stat strong {
  font-size: 2rem;
  font-weight: 800;
  color: #16a34a;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.stat span {
  font-size: 0.9375rem;
  color: #64748b;
}

.dashboard-card {
  background: white;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 24px 80px rgba(0, 0, 0, 0.12);
  border: 1px solid rgba(22, 163, 74, 0.1);
}

.card-header {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  padding: 1.25rem 1.5rem;
  display: flex;
  gap: 0.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.card-header span {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #cbd5e1;
}

.card-content {
  padding: 2rem;
}

.card-stats {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

.card-stat {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  border-radius: 12px;
}

.card-stat.green {
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.card-stat.blue {
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
}

.card-stat i {
  font-size: 1.5rem;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
}

.card-stat.green i {
  background: #16a34a;
  color: white;
}

.card-stat.blue i {
  background: #3b82f6;
  color: white;
}

.card-stat small {
  display: block;
  font-size: 0.75rem;
  color: #64748b;
  margin-bottom: 0.25rem;
}

.card-stat strong {
  display: block;
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
}

.card-chart {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 0.5rem;
  height: 120px;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-radius: 12px;
  padding: 1rem;
}

.chart-bar {
  flex: 1;
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  border-radius: 6px 6px 0 0;
  transition: all 0.3s;
}

.chart-bar:hover {
  opacity: 0.8;
}

/* Recursos Section */
.recursos-section {
  padding: 6rem 0;
  background: white;
}

.section-title {
  text-align: center;
  margin-bottom: 4rem;
}

.title-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1.25rem;
  background: linear-gradient(135deg, rgba(22, 163, 74, 0.1) 0%, rgba(21, 128, 61, 0.1) 100%);
  color: #16a34a;
  border-radius: 50px;
  font-weight: 600;
  font-size: 0.875rem;
  margin-bottom: 1.5rem;
}

.section-title h2 {
  font-size: 2.75rem;
  font-weight: 900;
  color: #1e293b;
  margin-bottom: 1rem;
}

.section-title p {
  font-size: 1.125rem;
  color: #64748b;
  max-width: 600px;
  margin: 0 auto;
}

.recursos-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}

.recurso-card {
  background: white;
  padding: 2.5rem;
  border-radius: 20px;
  border: 2px solid #f1f5f9;
  transition: all 0.3s;
}

.recurso-card:hover {
  border-color: #16a34a;
  transform: translateY(-6px);
  box-shadow: 0 16px 48px rgba(22, 163, 74, 0.12);
}

.recurso-icon {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
  color: white;
  margin-bottom: 1.5rem;
}

.recurso-icon.green {
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  box-shadow: 0 8px 24px rgba(22, 163, 74, 0.3);
}

.recurso-icon.blue {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
}

.recurso-icon.yellow {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3);
}

.recurso-icon.red {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3);
}

.recurso-icon.purple {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  box-shadow: 0 8px 24px rgba(139, 92, 246, 0.3);
}

.recurso-icon.cyan {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  box-shadow: 0 8px 24px rgba(6, 182, 212, 0.3);
}

.recurso-card h3 {
  font-size: 1.375rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 1rem;
}

.recurso-card p {
  color: #64748b;
  line-height: 1.6;
  font-size: 0.9375rem;
}

/* Funcionalidades Section */
.funcionalidades-section {
  padding: 6rem 0;
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
}

.funcionalidades-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}

.func-card {
  background: white;
  border-radius: 20px;
  padding: 2.5rem;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
  transition: all 0.3s;
}

.func-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 32px rgba(22, 163, 74, 0.12);
}

.func-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.func-header i {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  color: white;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  box-shadow: 0 4px 16px rgba(22, 163, 74, 0.3);
}

.func-header h3 {
  font-size: 1.25rem;
  font-weight: 800;
  color: #1e293b;
}

.func-card ul {
  list-style: none;
  padding: 0;
}

.func-card li {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #64748b;
  margin-bottom: 0.875rem;
  font-size: 0.9375rem;
  line-height: 1.5;
}

.func-card li i {
  color: #16a34a;
  font-size: 1rem;
  flex-shrink: 0;
}

/* CTA Section */
.cta-section {
  padding: 6rem 0;
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  text-align: center;
  color: white;
  position: relative;
  overflow: hidden;
}

.cta-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
  background-size: 30px 30px;
}

.cta-content {
  position: relative;
  z-index: 1;
}

.cta-content h2 {
  font-size: 3rem;
  font-weight: 900;
  margin-bottom: 1.25rem;
}

.cta-content p {
  font-size: 1.25rem;
  margin-bottom: 2.5rem;
  opacity: 0.95;
}

.btn-cta {
  padding: 1.25rem 2.5rem;
  background: white;
  color: #16a34a;
  text-decoration: none;
  font-weight: 800;
  font-size: 1.125rem;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  transition: all 0.3s;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.btn-cta:hover {
  transform: translateY(-3px) scale(1.02);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
}

/* Contato Section */
.contato-section {
  padding: 6rem 0;
  background: white;
}

.contato-grid {
  display: grid;
  grid-template-columns: 1fr 1.5fr;
  gap: 4rem;
  margin-top: 3rem;
}

.contato-info {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
  border-radius: 16px;
  border: 2px solid #e5e7eb;
  transition: all 0.3s;
}

.info-item:hover {
  border-color: #16a34a;
  transform: translateX(6px);
}

.info-icon {
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  color: white;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.375rem;
  flex-shrink: 0;
  box-shadow: 0 4px 16px rgba(22, 163, 74, 0.3);
}

.info-text h4 {
  font-size: 1.125rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.info-text p {
  color: #64748b;
  font-size: 0.9375rem;
}

.contato-form {
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
  padding: 3rem;
  border-radius: 20px;
  border: 2px solid #e5e7eb;
}

.contato-form input,
.contato-form textarea {
  width: 100%;
  padding: 1rem 1.25rem;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 1rem;
  margin-bottom: 1.5rem;
  transition: all 0.3s;
  background: white;
}

.contato-form input:focus,
.contato-form textarea:focus {
  outline: none;
  border-color: #16a34a;
  box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.1);
}

.btn-form {
  width: 100%;
  padding: 1.125rem;
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.625rem;
  transition: all 0.3s;
  box-shadow: 0 4px 16px rgba(22, 163, 74, 0.3);
}

.btn-form:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(22, 163, 74, 0.4);
}

/* Footer */
.footer {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  color: white;
  padding: 4rem 0 2rem;
}

.footer-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr 1fr 1fr;
  gap: 3rem;
  margin-bottom: 3rem;
}

.footer-about p {
  margin-top: 1.25rem;
  color: #94a3b8;
  line-height: 1.6;
  font-size: 0.9375rem;
}

.footer-col h4 {
  font-size: 1.125rem;
  font-weight: 800;
  margin-bottom: 1.25rem;
  color: white;
}

.footer-col a {
  display: block;
  color: #94a3b8;
  text-decoration: none;
  margin-bottom: 0.875rem;
  transition: all 0.3s;
  font-size: 0.9375rem;
}

.footer-col a:hover {
  color: #16a34a;
  transform: translateX(4px);
}

.social-links {
  display: flex;
  gap: 1rem;
}

.social-links a {
  width: 44px;
  height: 44px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  font-size: 1.125rem;
  color: white;
}

.social-links a:hover {
  background: #16a34a;
  transform: translateY(-3px);
  color: white;
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.4);
}

.footer-bottom {
  text-align: center;
  padding-top: 2.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  color: #94a3b8;
  font-size: 0.9375rem;
}

/* Responsive */
@media (max-width: 1024px) {
  .hero-grid {
    grid-template-columns: 1fr;
  }

  .recursos-grid,
  .funcionalidades-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .mobile-menu-btn {
    display: block;
  }

  .nav-links {
    position: fixed;
    top: 73px;
    left: 0;
    right: 0;
    background: white;
    flex-direction: column;
    padding: 2rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    transform: translateY(-100%);
    opacity: 0;
    transition: all 0.3s;
  }

  .nav-links.active {
    transform: translateY(0);
    opacity: 1;
  }

  .nav-actions {
    display: none;
  }

  .hero-title {
    font-size: 2.75rem;
  }

  .recursos-grid,
  .funcionalidades-grid,
  .contato-grid {
    grid-template-columns: 1fr;
  }

  .footer-grid {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .social-links {
    justify-content: center;
  }
}
</style>
