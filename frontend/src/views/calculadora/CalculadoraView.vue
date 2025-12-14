<template>
  <div class="calculadora-container">
    <!-- Header -->
    <div class="card">
      <div class="header-content">
        <div>
          <h2 class="text-2xl font-semibold text-900 m-0 mb-2">
            Calculadora Pecuária
          </h2>
          <p class="text-600 mt-0 mb-0">Ferramentas para cálculos zootécnicos e análise de rentabilidade</p>
        </div>
      </div>
    </div>

    <!-- Calculadoras -->
    <div class="calculadoras-grid">
      <!-- 1. GPD - Ganho de Peso Diário -->
      <div class="card calc-card">
        <div class="calc-header">
          <div class="calc-icon" style="background: #dbeafe; color: #2563eb;">
            <i class="fas fa-weight"></i>
          </div>
          <h3 class="calc-title">GPD - Ganho de Peso Diário</h3>
        </div>
        
        <div class="calc-form">
          <NumberInput 
            label="Peso Inicial (kg)" 
            v-model="gpd.pesoInicial" 
            placeholder="Ex: 180"
            :step="0.1"
          />
          <NumberInput 
            label="Peso Final (kg)" 
            v-model="gpd.pesoFinal" 
            placeholder="Ex: 450"
            :step="0.1"
          />
          <NumberInput 
            label="Dias de Confinamento" 
            v-model="gpd.dias" 
            placeholder="Ex: 120"
            :step="1"
          />
          
          <Button 
            label="Calcular GPD" 
            icon="pi pi-calculator" 
            @click="calcularGPD"
            class="w-full"
          />
        </div>

        <div v-if="gpd.resultado" class="calc-resultado">
          <div class="resultado-box gpd">
            <span class="resultado-label">GPD (Ganho Peso Diário)</span>
            <span class="resultado-valor">{{ gpd.resultado.gpd }} kg/dia</span>
          </div>
          <div class="resultado-detalhes">
            <div class="detalhe-item">
              <span class="detalhe-label">Ganho Total:</span>
              <span class="detalhe-valor">{{ gpd.resultado.ganhoTotal }} kg</span>
            </div>
            <div class="detalhe-item">
              <span class="detalhe-label">Taxa Crescimento:</span>
              <span class="detalhe-valor">{{ gpd.resultado.taxaCrescimento }}%</span>
            </div>
            <div class="detalhe-item">
              <span class="detalhe-label">Classificação:</span>
              <span class="detalhe-valor" :class="gpd.resultado.classificacaoClass">
                {{ gpd.resultado.classificacao }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Conversão Alimentar -->
      <div class="card calc-card">
        <div class="calc-header">
          <div class="calc-icon" style="background: #dcfce7; color: #16a34a;">
            <i class="fas fa-utensils"></i>
          </div>
          <h3 class="calc-title">Conversão Alimentar (CA)</h3>
        </div>
        
        <div class="calc-form">
          <NumberInput 
            label="Consumo Total de Ração (kg)" 
            v-model="ca.consumoTotal" 
            placeholder="Ex: 900"
            :step="0.1"
          />
          <NumberInput 
            label="Ganho de Peso Total (kg)" 
            v-model="ca.ganhoPeso" 
            placeholder="Ex: 150"
            :step="0.1"
          />
          
          <Button 
            label="Calcular CA" 
            icon="pi pi-calculator" 
            @click="calcularCA"
            class="w-full"
          />
        </div>

        <div v-if="ca.resultado" class="calc-resultado">
          <div class="resultado-box ca">
            <span class="resultado-label">Conversão Alimentar</span>
            <span class="resultado-valor">{{ ca.resultado.ca }} : 1</span>
          </div>
          <div class="resultado-detalhes">
            <div class="detalhe-item">
              <span class="detalhe-label">Eficiência:</span>
              <span class="detalhe-valor" :class="ca.resultado.eficienciaClass">
                {{ ca.resultado.eficiencia }}
              </span>
            </div>
            <div class="detalhe-item">
              <span class="detalhe-label">Kg Ração/kg Ganho:</span>
              <span class="detalhe-valor">{{ ca.resultado.ca }} kg</span>
            </div>
            <div class="detalhe-item">
              <span class="detalhe-label">Interpretação:</span>
              <span class="detalhe-valor">{{ ca.resultado.interpretacao }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Custo por Arroba -->
      <div class="card calc-card">
        <div class="calc-header">
          <div class="calc-icon" style="background: #fef3c7; color: #f59e0b;">
            <i class="fas fa-money-bill-wave"></i>
          </div>
          <h3 class="calc-title">Custo por Arroba</h3>
        </div>
        
        <div class="calc-form">
          <NumberInput 
            label="Custo Total (R$)" 
            v-model="custoPorArroba.custoTotal" 
            placeholder="Ex: 5000"
            :step="0.01"
          />
          <NumberInput 
            label="Peso Vivo Final (kg)" 
            v-model="custoPorArroba.pesoFinal" 
            placeholder="Ex: 450"
            :step="0.1"
          />
          <NumberInput 
            label="Rendimento Carcaça (%)" 
            v-model="custoPorArroba.rendimentoCarcaca" 
            placeholder="Ex: 52"
            :step="0.1"
          />
          
          <Button 
            label="Calcular Custo" 
            icon="pi pi-calculator" 
            @click="calcularCustoPorArroba"
            class="w-full"
          />
        </div>

        <div v-if="custoPorArroba.resultado" class="calc-resultado">
          <div class="resultado-box custo">
            <span class="resultado-label">Custo por Arroba</span>
            <span class="resultado-valor">R$ {{ custoPorArroba.resultado.custoArroba }}</span>
          </div>
          <div class="resultado-detalhes">
            <div class="detalhe-item">
              <span class="detalhe-label">Peso Carcaça:</span>
              <span class="detalhe-valor">{{ custoPorArroba.resultado.pesoCarcaca }} kg</span>
            </div>
            <div class="detalhe-item">
              <span class="detalhe-label">Total Arrobas:</span>
              <span class="detalhe-valor">{{ custoPorArroba.resultado.totalArrobas }} @</span>
            </div>
            <div class="detalhe-item">
              <span class="detalhe-label">Custo por Kg:</span>
              <span class="detalhe-valor">R$ {{ custoPorArroba.resultado.custoPorKg }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. Projeção de Lucro -->
      <div class="card calc-card">
        <div class="calc-header">
          <div class="calc-icon" style="background: #fee2e2; color: #dc2626;">
            <i class="fas fa-chart-line"></i>
          </div>
          <h3 class="calc-title">Projeção de Lucro</h3>
        </div>
        
        <div class="calc-form">
          <NumberInput 
            label="Preço de Compra (R$/kg)" 
            v-model="lucro.precoCompra" 
            placeholder="Ex: 10"
            :step="0.01"
          />
          <NumberInput 
            label="Peso Entrada (kg)" 
            v-model="lucro.pesoEntrada" 
            placeholder="Ex: 180"
            :step="0.1"
          />
          <NumberInput 
            label="Custo Total Produção (R$)" 
            v-model="lucro.custoProducao" 
            placeholder="Ex: 5000"
            :step="0.01"
          />
          <NumberInput 
            label="Preço Venda Arroba (R$/@)" 
            v-model="lucro.precoVenda" 
            placeholder="Ex: 280"
            :step="0.01"
          />
          <NumberInput 
            label="Peso Saída (kg)" 
            v-model="lucro.pesoSaida" 
            placeholder="Ex: 450"
            :step="0.1"
          />
          <NumberInput 
            label="Rendimento Carcaça (%)" 
            v-model="lucro.rendimento" 
            placeholder="Ex: 52"
            :step="0.1"
          />
          
          <Button 
            label="Calcular Lucro" 
            icon="pi pi-calculator" 
            @click="calcularLucro"
            class="w-full"
          />
        </div>

        <div v-if="lucro.resultado" class="calc-resultado">
          <div class="resultado-box lucro">
            <span class="resultado-label">Lucro Líquido</span>
            <span class="resultado-valor" :class="lucro.resultado.lucroClass">
              R$ {{ lucro.resultado.lucroLiquido }}
            </span>
          </div>
          <div class="resultado-detalhes">
            <div class="detalhe-item">
              <span class="detalhe-label">Custo Compra:</span>
              <span class="detalhe-valor text-danger">R$ {{ lucro.resultado.custoCompra }}</span>
            </div>
            <div class="detalhe-item">
              <span class="detalhe-label">Custo Produção:</span>
              <span class="detalhe-valor text-danger">R$ {{ lucro.resultado.custoProducao }}</span>
            </div>
            <div class="detalhe-item">
              <span class="detalhe-label">Receita Venda:</span>
              <span class="detalhe-valor text-success">R$ {{ lucro.resultado.receitaVenda }}</span>
            </div>
            <div class="detalhe-item">
              <span class="detalhe-label">ROI:</span>
              <span class="detalhe-valor" :class="lucro.resultado.roiClass">
                {{ lucro.resultado.roi }}%
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- 5. Tempo para Peso Desejado -->
      <div class="card calc-card">
        <div class="calc-header">
          <div class="calc-icon" style="background: #f3e8ff; color: #8b5cf6;">
            <i class="fas fa-clock"></i>
          </div>
          <h3 class="calc-title">Tempo para Peso Desejado</h3>
        </div>
        
        <div class="calc-form">
          <NumberInput 
            label="Peso Atual (kg)" 
            v-model="tempo.pesoAtual" 
            placeholder="Ex: 250"
            :step="0.1"
          />
          <NumberInput 
            label="Peso Desejado (kg)" 
            v-model="tempo.pesoDesejado" 
            placeholder="Ex: 450"
            :step="0.1"
          />
          <NumberInput 
            label="GPD Estimado (kg/dia)" 
            v-model="tempo.gpdEstimado" 
            placeholder="Ex: 1.2"
            :step="0.01"
          />
          
          <Button 
            label="Calcular Tempo" 
            icon="pi pi-calculator" 
            @click="calcularTempo"
            class="w-full"
          />
        </div>

        <div v-if="tempo.resultado" class="calc-resultado">
          <div class="resultado-box tempo">
            <span class="resultado-label">Tempo Necessário</span>
            <span class="resultado-valor">{{ tempo.resultado.dias }} dias</span>
          </div>
          <div class="resultado-detalhes">
            <div class="detalhe-item">
              <span class="detalhe-label">Meses:</span>
              <span class="detalhe-valor">{{ tempo.resultado.meses }} meses</span>
            </div>
            <div class="detalhe-item">
              <span class="detalhe-label">Ganho Necessário:</span>
              <span class="detalhe-valor">{{ tempo.resultado.ganhoNecessario }} kg</span>
            </div>
            <div class="detalhe-item">
              <span class="detalhe-label">Data Prevista:</span>
              <span class="detalhe-valor">{{ tempo.resultado.dataPrevista }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 6. Idade de Abate Ideal -->
      <div class="card calc-card">
        <div class="calc-header">
          <div class="calc-icon" style="background: #e0e7ff; color: #6366f1;">
            <i class="fas fa-calendar-check"></i>
          </div>
          <h3 class="calc-title">Idade de Abate Ideal</h3>
        </div>
        
        <div class="calc-form">
          <CalendarInput 
            label="Data de Nascimento" 
            v-model="idade.dataNascimento"
            dateFormat="dd/mm/yy"
          />
          <NumberInput 
            label="Peso Atual (kg)" 
            v-model="idade.pesoAtual" 
            placeholder="Ex: 380"
            :step="0.1"
          />
          <NumberInput 
            label="Peso Abate Desejado (kg)" 
            v-model="idade.pesoAbate" 
            placeholder="Ex: 450"
            :step="0.1"
          />
          <NumberInput 
            label="GPD Médio (kg/dia)" 
            v-model="idade.gpdMedio" 
            placeholder="Ex: 1.0"
            :step="0.01"
          />
          
          <Button 
            label="Calcular Idade" 
            icon="pi pi-calculator" 
            @click="calcularIdadeAbate"
            class="w-full"
          />
        </div>

        <div v-if="idade.resultado" class="calc-resultado">
          <div class="resultado-box idade">
            <span class="resultado-label">Idade Atual</span>
            <span class="resultado-valor">{{ idade.resultado.idadeAtual }} meses</span>
          </div>
          <div class="resultado-detalhes">
            <div class="detalhe-item">
              <span class="detalhe-label">Dias Faltantes:</span>
              <span class="detalhe-valor">{{ idade.resultado.diasFaltantes }} dias</span>
            </div>
            <div class="detalhe-item">
              <span class="detalhe-label">Idade no Abate:</span>
              <span class="detalhe-valor">{{ idade.resultado.idadeAbate }} meses</span>
            </div>
            <div class="detalhe-item">
              <span class="detalhe-label">Data Abate Prevista:</span>
              <span class="detalhe-valor">{{ idade.resultado.dataAbate }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { NumberInput, CalendarInput } from '../../components/forms'

// Estados das calculadoras
const gpd = reactive({
  pesoInicial: '',
  pesoFinal: '',
  dias: '',
  resultado: null as any
})

const ca = reactive({
  consumoTotal: '',
  ganhoPeso: '',
  resultado: null as any
})

const custoPorArroba = reactive({
  custoTotal: '',
  pesoFinal: '',
  rendimentoCarcaca: '',
  resultado: null as any
})

const lucro = reactive({
  precoCompra: '',
  pesoEntrada: '',
  custoProducao: '',
  precoVenda: '',
  pesoSaida: '',
  rendimento: '',
  resultado: null as any
})

const tempo = reactive({
  pesoAtual: '',
  pesoDesejado: '',
  gpdEstimado: '',
  resultado: null as any
})

const idade = reactive({
  dataNascimento: null as Date | null,
  pesoAtual: '',
  pesoAbate: '',
  gpdMedio: '',
  resultado: null as any
})

// Funções de Cálculo
const calcularGPD = () => {
  const pesoInicial = parseFloat(gpd.pesoInicial)
  const pesoFinal = parseFloat(gpd.pesoFinal)
  const dias = parseFloat(gpd.dias)

  const ganhoTotal = pesoFinal - pesoInicial
  const gpdCalculado = ganhoTotal / dias
  const taxaCrescimento = ((pesoFinal - pesoInicial) / pesoInicial) * 100

  let classificacao = ''
  let classificacaoClass = ''
  
  if (gpdCalculado >= 1.5) {
    classificacao = 'Excelente'
    classificacaoClass = 'text-success'
  } else if (gpdCalculado >= 1.2) {
    classificacao = 'Bom'
    classificacaoClass = 'text-success'
  } else if (gpdCalculado >= 0.8) {
    classificacao = 'Regular'
    classificacaoClass = 'text-warning'
  } else {
    classificacao = 'Baixo'
    classificacaoClass = 'text-danger'
  }

  gpd.resultado = {
    gpd: gpdCalculado.toFixed(2),
    ganhoTotal: ganhoTotal.toFixed(1),
    taxaCrescimento: taxaCrescimento.toFixed(1),
    classificacao,
    classificacaoClass
  }
}

const calcularCA = () => {
  const consumo = parseFloat(ca.consumoTotal)
  const ganho = parseFloat(ca.ganhoPeso)

  const caCalculada = consumo / ganho

  let eficiencia = ''
  let eficienciaClass = ''
  let interpretacao = ''

  if (caCalculada <= 6) {
    eficiencia = 'Excelente'
    eficienciaClass = 'text-success'
    interpretacao = 'CA muito eficiente'
  } else if (caCalculada <= 8) {
    eficiencia = 'Boa'
    eficienciaClass = 'text-success'
    interpretacao = 'CA dentro do esperado'
  } else if (caCalculada <= 10) {
    eficiencia = 'Regular'
    eficienciaClass = 'text-warning'
    interpretacao = 'CA pode melhorar'
  } else {
    eficiencia = 'Baixa'
    eficienciaClass = 'text-danger'
    interpretacao = 'CA precisa atenção'
  }

  ca.resultado = {
    ca: caCalculada.toFixed(2),
    eficiencia,
    eficienciaClass,
    interpretacao
  }
}

const calcularCustoPorArroba = () => {
  const custo = parseFloat(custoPorArroba.custoTotal)
  const peso = parseFloat(custoPorArroba.pesoFinal)
  const rendimento = parseFloat(custoPorArroba.rendimentoCarcaca)

  const pesoCarcaca = peso * (rendimento / 100)
  const totalArrobas = pesoCarcaca / 15
  const custoArrobaCalc = custo / totalArrobas
  const custoPorKg = custo / pesoCarcaca

  custoPorArroba.resultado = {
    custoArroba: custoArrobaCalc.toFixed(2),
    pesoCarcaca: pesoCarcaca.toFixed(1),
    totalArrobas: totalArrobas.toFixed(2),
    custoPorKg: custoPorKg.toFixed(2)
  }
}

const calcularLucro = () => {
  const precoCompra = parseFloat(lucro.precoCompra)
  const pesoEntrada = parseFloat(lucro.pesoEntrada)
  const custoProducao = parseFloat(lucro.custoProducao)
  const precoVenda = parseFloat(lucro.precoVenda)
  const pesoSaida = parseFloat(lucro.pesoSaida)
  const rendimento = parseFloat(lucro.rendimento)

  const custoCompra = precoCompra * pesoEntrada
  const pesoCarcaca = pesoSaida * (rendimento / 100)
  const arrobas = pesoCarcaca / 15
  const receitaVenda = arrobas * precoVenda
  
  const custoTotal = custoCompra + custoProducao
  const lucroLiquido = receitaVenda - custoTotal
  const roi = (lucroLiquido / custoTotal) * 100

  lucro.resultado = {
    custoCompra: custoCompra.toFixed(2),
    custoProducao: custoProducao.toFixed(2),
    receitaVenda: receitaVenda.toFixed(2),
    lucroLiquido: lucroLiquido.toFixed(2),
    lucroClass: lucroLiquido >= 0 ? 'text-success' : 'text-danger',
    roi: roi.toFixed(1),
    roiClass: roi >= 20 ? 'text-success' : roi >= 10 ? 'text-warning' : 'text-danger'
  }
}

const calcularTempo = () => {
  const pesoAtual = parseFloat(tempo.pesoAtual)
  const pesoDesejado = parseFloat(tempo.pesoDesejado)
  const gpd = parseFloat(tempo.gpdEstimado)

  const ganhoNecessario = pesoDesejado - pesoAtual
  const dias = ganhoNecessario / gpd
  const meses = (dias / 30).toFixed(1)
  
  const dataAtual = new Date()
  const dataPrevista = new Date(dataAtual.getTime() + (dias * 24 * 60 * 60 * 1000))

  tempo.resultado = {
    dias: Math.ceil(dias),
    meses,
    ganhoNecessario: ganhoNecessario.toFixed(1),
    dataPrevista: dataPrevista.toLocaleDateString('pt-BR')
  }
}

const calcularIdadeAbate = () => {
  if (!idade.dataNascimento) return

  const pesoAtual = parseFloat(idade.pesoAtual)
  const pesoAbate = parseFloat(idade.pesoAbate)
  const gpd = parseFloat(idade.gpdMedio)

  const hoje = new Date()
  const nascimento = new Date(idade.dataNascimento)
  const diffTime = Math.abs(hoje.getTime() - nascimento.getTime())
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  const idadeAtualMeses = (diffDays / 30).toFixed(0)

  const ganhoNecessario = pesoAbate - pesoAtual
  const diasFaltantes = Math.ceil(ganhoNecessario / gpd)
  
  const dataAbate = new Date(hoje.getTime() + (diasFaltantes * 24 * 60 * 60 * 1000))
  const idadeAbateDias = diffDays + diasFaltantes
  const idadeAbateMeses = (idadeAbateDias / 30).toFixed(0)

  idade.resultado = {
    idadeAtual: idadeAtualMeses,
    diasFaltantes,
    idadeAbate: idadeAbateMeses,
    dataAbate: dataAbate.toLocaleDateString('pt-BR')
  }
}
</script>

<style scoped>
.calculadora-container {
  padding: 1.5rem;
  background: #f8fafc;
  min-height: 100vh;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 1.5rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.calculadoras-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
}

.calc-card {
  padding: 0;
  overflow: hidden;
}

.calc-header {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  border-bottom: 2px solid #e5e7eb;
}

.calc-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  flex-shrink: 0;
}

.calc-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.calc-form {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.calc-resultado {
  padding: 1.5rem;
  background: #f8fafc;
  border-top: 2px solid #e5e7eb;
}

.resultado-box {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  margin-bottom: 1rem;
  border-left: 4px solid;
}

.resultado-box.gpd {
  border-left-color: #2563eb;
}

.resultado-box.ca {
  border-left-color: #16a34a;
}

.resultado-box.custo {
  border-left-color: #f59e0b;
}

.resultado-box.lucro {
  border-left-color: #dc2626;
}

.resultado-box.tempo {
  border-left-color: #8b5cf6;
}

.resultado-box.idade {
  border-left-color: #6366f1;
}

.resultado-label {
  display: block;
  font-size: 0.875rem;
  color: #64748b;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.resultado-valor {
  display: block;
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
}

.resultado-detalhes {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.detalhe-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: white;
  border-radius: 8px;
}

.detalhe-label {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
}

.detalhe-valor {
  font-size: 0.875rem;
  color: #1e293b;
  font-weight: 700;
}

.text-success {
  color: #16a34a !important;
}

.text-warning {
  color: #f59e0b !important;
}

.text-danger {
  color: #dc2626 !important;
}

@media (max-width: 768px) {
  .calculadoras-grid {
    grid-template-columns: 1fr;
  }
}
</style>

