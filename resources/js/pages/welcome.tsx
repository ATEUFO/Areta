import { Head, Link, usePage } from '@inertiajs/react';
import { login, register } from '@/routes';
import AppLogoIcon from '@/components/app-logo-icon';

export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Areta — Plateforme d'Excellence Pédagogique à Domicile" />

            <div className="min-h-screen bg-marble text-aegean selection:bg-gold/30 selection:text-aegean">

                {/* ── HEADER ───────────────────────────────────────────────────────────── */}
                <header className="sticky top-0 z-50 glass-light border-b border-marble-stone/50 px-6 py-4 header-gold-line">
                    <div className="mx-auto flex max-w-7xl items-center justify-between">
                        {/* Logo */}
                        <div className="flex items-center gap-3">
                            <div className="flex size-9 items-center justify-center rounded-md bg-gold text-aegean shadow-gold">
                                <AppLogoIcon className="size-5 text-aegean" />
                            </div>
                            <div>
                                <span className="font-display text-xl font-bold tracking-widest text-aegean dark:text-gold uppercase leading-none block">
                                    ARETA
                                </span>
                                <span className="font-serif text-[11px] tracking-widest text-stone-500 uppercase block">
                                    Répétition & Suivi Pédagogique
                                </span>
                            </div>
                        </div>

                        {/* Navigation Actions */}
                        <nav className="flex items-center gap-3">
                            {auth?.user ? (
                                <Link
                                    href="/dashboard"
                                    className="btn-primary-areta text-sm"
                                >
                                    Mon Tableau de Bord
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={login()}
                                        className="btn-aegean-areta text-sm"
                                    >
                                        Connexion
                                    </Link>
                                    <Link
                                        href={register()}
                                        className="btn-primary-areta text-sm"
                                    >
                                        Rejoindre Areta
                                    </Link>
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                {/* ── HERO SECTION ─────────────────────────────────────────────────────── */}
                <section className="relative overflow-hidden meander-section bg-gradient-hero text-marble py-24 px-6 lg:py-32">
                    <div className="mx-auto max-w-5xl text-center relative z-10">

                        {/* Badge de réputation */}
                        <div className="inline-flex items-center gap-2 rounded-full border border-gold/40 bg-gold/10 px-4 py-1.5 text-xs font-semibold text-gold mb-8 backdrop-blur-md">
                            <span className="inline-block size-2 rounded-full bg-gold animate-ping" />
                             Plateforme de Répétition à Domicile 100% Autonome & IA
                        </div>

                        {/* Titre Principal — Cinzel */}
                        <h1 className="font-display text-4xl sm:text-5xl lg:text-6xl font-bold tracking-wider text-marble leading-tight mb-6">
                            L'Excellence Pédagogique <br />
                            <span className="text-gradient-gold">Guidée par l'Intelligence Artificielle</span>
                        </h1>

                        {/* Description — Cormorant Garamond */}
                        <p className="font-serif text-xl sm:text-2xl text-marble/80 max-w-3xl mx-auto mb-10 leading-relaxed">
                            Mise en relation directe entre parents et répétiteurs qualifiés,
                            suivi des notes en temps réel et bilans pédagogiques automatisés par IA.
                        </p>

                        {/* CTA Buttons */}
                        <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
                            <Link href={register()} className="btn-primary-areta text-base py-3.5 px-8 w-full sm:w-auto">
                                Trouver un Répétiteur 🎓
                            </Link>
                            <a href="#fonctionnalites" className="btn-aegean-areta text-base py-3.5 px-8 w-full sm:w-auto bg-marble/10 text-marble border-marble/20 hover:bg-marble/20">
                                Découvrir le Fonctionnement
                            </a>
                        </div>

                        {/* Counter Widget */}
                        <div className="mt-16 grid grid-cols-2 md:grid-cols-4 gap-6 pt-10 border-t border-marble/10 max-w-4xl mx-auto">
                            <div>
                                <span className="font-display text-3xl font-bold text-gold">100%</span>
                                <p className="font-ui text-xs text-marble/70 mt-1">Self-Service Autonome</p>
                            </div>
                            <div>
                                <span className="font-display text-3xl font-bold text-gold">0 /20</span>
                                <p className="font-ui text-xs text-marble/70 mt-1">Note & Suivi en Temps Réel</p>
                            </div>
                            <div>
                                <span className="font-display text-3xl font-bold text-gold">IA</span>
                                <p className="font-ui text-xs text-marble/70 mt-1">Diagnostics Automatisés</p>
                            </div>
                            <div>
                                <span className="font-display text-3xl font-bold text-gold">REST</span>
                                <p className="font-ui text-xs text-marble/70 mt-1">API Native Multi-Plateforme</p>
                            </div>
                        </div>

                    </div>
                </section>

                {/* ── LES 5 MODULES CLÉS ────────────────────────────────────────────────── */}
                <section id="fonctionnalites" className="py-20 px-6 max-w-7xl mx-auto">
                    <div className="text-center mb-16">
                        <span className="badge-gold uppercase tracking-widest text-[11px] font-bold mb-3">
                            Architecture & Modules
                        </span>
                        <h2 className="font-display text-3xl sm:text-4xl font-bold text-aegean mt-2">
                            Une Plateforme Conçue pour la Réussite
                        </h2>
                        <p className="font-serif text-lg text-stone-600 max-w-2xl mx-auto mt-3">
                            Du recrutement du professeur à l'analyse algorithmique des lacunes.
                        </p>
                    </div>

                    <div className="grid md:grid-cols-3 gap-8">

                        {/* Card 1: Job Board */}
                        <div className="card-areta p-8 flex flex-col justify-between">
                            <div>
                                <div className="size-12 rounded-lg bg-aegean-pale text-aegean flex items-center justify-center text-2xl mb-6">
                                    🏛️
                                </div>
                                <h3 className="font-serif text-2xl font-bold text-aegean mb-3">
                                    Place de Marché Géolocalisée
                                </h3>
                                <p className="font-ui text-sm text-stone-600 leading-relaxed">
                                    Les parents publient leurs besoins (ville, quartier, matière, fréquence). Les répétiteurs consultent le flux d'offres en temps réel sans rechargement de page.
                                </p>
                            </div>
                            <div className="mt-6 pt-4 border-t border-marble-stone flex items-center justify-between text-xs text-stone-500 font-medium">
                                <span>Indépendance des Offres</span>
                                <span className="text-gold font-bold">Self-Service →</span>
                            </div>
                        </div>

                        {/* Card 2: Core Tracker */}
                        <div className="card-areta p-8 flex flex-col justify-between">
                            <div>
                                <div className="size-12 rounded-lg bg-gold-pale text-bronze flex items-center justify-center text-2xl mb-6">
                                    📜
                                </div>
                                <h3 className="font-serif text-2xl font-bold text-aegean mb-3">
                                    Suivi Pédagogique (Core Tracker)
                                </h3>
                                <p className="font-ui text-sm text-stone-600 leading-relaxed">
                                    Le carnet de liaison moderne. Après chaque séance, le répétiteur saisit une note sur 20 et son rapport. La planification suivante est bloquée tant que le rapport n'est pas rempli.
                                </p>
                            </div>
                            <div className="mt-6 pt-4 border-t border-marble-stone flex items-center justify-between text-xs text-stone-500 font-medium">
                                <span>Moyenne Recalculée</span>
                                <span className="text-gold font-bold">Règle de Blocage →</span>
                            </div>
                        </div>

                        {/* Card 3: AI Evaluator */}
                        <div className="card-areta p-8 flex flex-col justify-between">
                            <div>
                                <div className="size-12 rounded-lg bg-terracotta-pale text-terracotta flex items-center justify-center text-2xl mb-6">
                                    🧠
                                </div>
                                <h3 className="font-serif text-2xl font-bold text-aegean mb-3">
                                    Intelligence Artificielle (AI Evaluator)
                                </h3>
                                <p className="font-ui text-sm text-stone-600 leading-relaxed">
                                    Toutes les 3 à 5 séances, un job d'arrière-plan alimente les LLM pour détecter les lacunes, estimer la courbe de progression et fournir un plan d'action ciblé.
                                </p>
                            </div>
                            <div className="mt-6 pt-4 border-t border-marble-stone flex items-center justify-between text-xs text-stone-500 font-medium">
                                <span>DeepSeek & OpenAI</span>
                                <span className="text-terracotta font-bold">Format JSON Strict →</span>
                            </div>
                        </div>

                    </div>
                </section>

                {/* ── STEPPER : COMMENT ÇA MARCHE ─────────────────────────────────────── */}
                <section className="py-20 bg-marble-dark/40 border-y border-marble-stone meander-section">
                    <div className="max-w-7xl mx-auto px-6">
                        <div className="text-center mb-16">
                            <h2 className="font-display text-3xl sm:text-4xl font-bold text-aegean">
                                Le Flux de Travail en 4 Étapes
                            </h2>
                            <p className="font-serif text-lg text-stone-600 mt-2">
                                Un processus fluide de l'offre initiale au bilan automatisé.
                            </p>
                        </div>

                        <div className="grid md:grid-cols-4 gap-6">

                            {/* Step 1 */}
                            <div className="bg-marble-warm p-6 rounded-lg border border-marble-stone relative">
                                <span className="font-display text-4xl font-bold text-gold/40 absolute top-4 right-4">01</span>
                                <h4 className="font-ui text-base font-bold text-aegean mb-2">1. Offre d'Emploi</h4>
                                <p className="font-ui text-xs text-stone-600">
                                    Le Parent définit son besoin : matière, niveau, fréquence et quartier.
                                </p>
                            </div>

                            {/* Step 2 */}
                            <div className="bg-marble-warm p-6 rounded-lg border border-marble-stone relative">
                                <span className="font-display text-4xl font-bold text-gold/40 absolute top-4 right-4">02</span>
                                <h4 className="font-ui text-base font-bold text-aegean mb-2">2. Match & Contrat</h4>
                                <p className="font-ui text-xs text-stone-600">
                                    Le Répétiteur postule. L'acceptation du parent génère le Contrat Pédagogique.
                                </p>
                            </div>

                            {/* Step 3 */}
                            <div className="bg-marble-warm p-6 rounded-lg border border-marble-stone relative">
                                <span className="font-display text-4xl font-bold text-gold/40 absolute top-4 right-4">03</span>
                                <h4 className="font-ui text-base font-bold text-aegean mb-2">3. Séance & Note</h4>
                                <p className="font-ui text-xs text-stone-600">
                                    Le cours est effectué, la note et le rapport textuel sont saisis en temps réel.
                                </p>
                            </div>

                            {/* Step 4 */}
                            <div className="bg-marble-warm p-6 rounded-lg border border-marble-stone relative">
                                <span className="font-display text-4xl font-bold text-gold/40 absolute top-4 right-4">04</span>
                                <h4 className="font-ui text-base font-bold text-aegean mb-2">4. Diagnostic IA</h4>
                                <p className="font-ui text-xs text-stone-600">
                                    L'IA analyse la tendance et affiche les alertes et conseils sur le tableau de bord.
                                </p>
                            </div>

                        </div>
                    </div>
                </section>

                {/* ── FOOTER ───────────────────────────────────────────────────────────── */}
                <footer className="bg-aegean text-marble py-12 px-6 border-t border-gold/20">
                    <div className="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
                        <div className="flex items-center gap-3">
                            <div className="flex size-8 items-center justify-center rounded-md bg-gold text-aegean">
                                <AppLogoIcon className="size-4 text-aegean" />
                            </div>
                            <span className="font-display text-lg font-bold tracking-widest text-gold uppercase">
                                ARETA
                            </span>
                        </div>

                        <p className="font-ui text-xs text-marble/60 text-center">
                            © {new Date().getFullYear()} Areta — Plateforme d'Excellence Pédagogique à Domicile.
                        </p>

                        <div className="flex items-center gap-4 text-xs">
                            <a href="/docs/api" className="text-gold hover:underline font-semibold">
                                Documentation API REST (OpenAPI)
                            </a>
                        </div>
                    </div>
                </footer>

            </div>
        </>
    );
}
