import { Head, Link, usePage } from '@inertiajs/react';
import { login, register } from '@/routes';
import AppLogoIcon from '@/components/app-logo-icon';
import { GlyphMatrix } from '@/components/ui/glyph-matrix';
import { AnimatedCircularProgressBar } from '@/components/ui/animated-circular-progress-bar';

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
                                    Excellence & Suivi Pédagogique
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

                {/* ── HERO SECTION (avec Magic UI GlyphMatrix & Photographie Africaine) ─── */}
                <section className="relative overflow-hidden bg-gradient-hero text-marble py-20 px-6 lg:py-28">

                    {/* Magic UI : Matrix de glyphes grecs (ΑΒΓΔ...) & symboles scientifiques */}
                    <div className="absolute inset-0 pointer-events-none opacity-25">
                        <GlyphMatrix
                            glyphs="ΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩ∑∫√π∞±1234567890"
                            color="#D4AF37"
                            cellSize={16}
                            mutationRate={0.03}
                            interval={80}
                            fadeBottom={0.85}
                        />
                    </div>

                    <div className="mx-auto max-w-7xl relative z-10 grid lg:grid-cols-12 gap-12 items-center">

                        {/* Col 1 : Texte & CTA */}
                        <div className="lg:col-span-7 text-left">
                            <div className="inline-flex items-center gap-2 rounded-full border border-gold/40 bg-gold/10 px-4 py-1.5 text-xs font-semibold text-gold mb-6 backdrop-blur-md">
                                <span className="inline-block size-2 rounded-full bg-gold animate-ping" />
                                 Répétition à Domicile 100% Autonome & IA
                            </div>

                            <h1 className="font-display text-4xl sm:text-5xl lg:text-6xl font-bold tracking-wider text-marble leading-tight mb-6">
                                L'Excellence Pédagogique <br />
                                <span className="text-gradient-gold">Guidée par l'IA</span>
                            </h1>

                            <p className="font-serif text-xl text-marble/85 max-w-2xl mb-8 leading-relaxed">
                                Mise en relation directe entre parents et répétiteurs d'élite.
                                Suivi des notes en temps réel et diagnostics pédagogiques automatisés par IA.
                            </p>

                            <div className="flex flex-col sm:flex-row items-center gap-4">
                                <Link href={register()} className="btn-primary-areta text-base py-3.5 px-8 w-full sm:w-auto text-center">
                                    Trouver un Répétiteur 🎓
                                </Link>
                                <a href="#repetiteurs" className="btn-aegean-areta text-base py-3.5 px-8 w-full sm:w-auto bg-marble/10 text-marble border-marble/20 hover:bg-marble/20 text-center">
                                    Voir les Répétiteurs
                                </a>
                            </div>

                            {/* Stat Widgets */}
                            <div className="mt-12 grid grid-cols-3 gap-6 pt-8 border-t border-marble/15 max-w-lg">
                                <div>
                                    <span className="font-display text-2xl sm:text-3xl font-bold text-gold">100%</span>
                                    <p className="font-ui text-xs text-marble/70 mt-1">Self-Service Autonome</p>
                                </div>
                                <div>
                                    <span className="font-display text-2xl sm:text-3xl font-bold text-gold">/20</span>
                                    <p className="font-ui text-xs text-marble/70 mt-1">Notes en Temps Réel</p>
                                </div>
                                <div>
                                    <span className="font-display text-2xl sm:text-3xl font-bold text-gold">JSON</span>
                                    <p className="font-ui text-xs text-marble/70 mt-1">API REST Native</p>
                                </div>
                            </div>
                        </div>

                        {/* Col 2 : Photographie Africaine de Répétition + Magic UI Circular Progress */}
                        <div className="lg:col-span-5 relative">
                            <div className="relative rounded-2xl overflow-hidden shadow-aegean border-2 border-gold/40">
                                <img
                                    src="/images/hero_tutoring.png"
                                    alt="Répétition à domicile en Afrique - Areta"
                                    className="w-full h-[440px] object-cover"
                                />
                                <div className="absolute inset-0 bg-gradient-to-t from-aegean-deep/90 via-aegean-deep/20 to-transparent" />

                                {/* Widget Glassmorphism avec Magic UI Animated Circular Progress Bar */}
                                <div className="absolute bottom-4 left-4 right-4 glass-dark p-4 rounded-xl flex items-center gap-4 text-marble border border-gold/30">
                                    <div className="shrink-0">
                                        <AnimatedCircularProgressBar
                                            value={92}
                                            min={0}
                                            max={100}
                                            gaugePrimaryColor="#D4AF37"
                                            gaugeSecondaryColor="rgba(212, 175, 55, 0.2)"
                                            className="size-16 text-xs text-gold font-display"
                                        />
                                    </div>
                                    <div>
                                        <span className="badge-gold text-[10px] mb-1">Indice de Réussite Élève</span>
                                        <h4 className="font-serif text-base font-bold text-marble">Progression Continue</h4>
                                        <p className="font-ui text-xs text-marble/70">
                                            +16.5% de moyenne sur les 5 dernières séances
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

                {/* ── SECTION RÉPÉTITEURS D'ÉLITE (Cartes avec photos réelles) ─────────── */}
                <section id="repetiteurs" className="py-20 px-6 max-w-7xl mx-auto">
                    <div className="text-center mb-16">
                        <span className="badge-gold uppercase tracking-widest text-[11px] font-bold mb-3">
                            Répétiteurs Vérifiés
                        </span>
                        <h2 className="font-display text-3xl sm:text-4xl font-bold text-aegean mt-2">
                            Des Enseignants de Confiance à Domicile
                        </h2>
                        <p className="font-serif text-lg text-stone-600 max-w-2xl mx-auto mt-3">
                            Profils rigoureusement sélectionnés et certifiés sur la plateforme Areta.
                        </p>
                    </div>

                    <div className="grid md:grid-cols-3 gap-8">

                        {/* Répétiteur 1 */}
                        <div className="card-areta p-6 flex flex-col justify-between">
                            <div>
                                <div className="relative rounded-lg overflow-hidden h-52 mb-5">
                                    <img
                                        src="/images/tutor_male.png"
                                        alt="Professeur Koffi - Répétiteur Areta"
                                        className="w-full h-full object-cover"
                                    />
                                    <span className="absolute top-3 right-3 badge-gold text-xs shadow-md">
                                        ★ 4.9 (48 avis)
                                    </span>
                                </div>
                                <h3 className="font-serif text-2xl font-bold text-aegean mb-1">
                                    M. Koffi Kouassi
                                </h3>
                                <p className="font-ui text-xs font-semibold text-gold mb-3 uppercase tracking-wider">
                                    Mathématiques & Physique · Lycée & Supérieur
                                </p>
                                <p className="font-ui text-sm text-stone-600 leading-relaxed">
                                    Ingénieur diplômé avec 6 ans d'expérience en cours particuliers à Abidjan (Cocody, Riviera).
                                </p>
                            </div>
                            <div className="mt-6 pt-4 border-t border-marble-stone flex items-center justify-between text-xs">
                                <span className="font-bold text-aegean">15 000 FCFA / h</span>
                                <span className="badge-aegean">Série de 12 rapports vus</span>
                            </div>
                        </div>

                        {/* Répétiteur 2 */}
                        <div className="card-areta p-6 flex flex-col justify-between">
                            <div>
                                <div className="relative rounded-lg overflow-hidden h-52 mb-5 bg-aegean flex items-center justify-center">
                                    <img
                                        src="/images/hero_tutoring.png"
                                        alt="Mme Kamara - Répétitrice Areta"
                                        className="w-full h-full object-cover"
                                    />
                                    <span className="absolute top-3 right-3 badge-gold text-xs shadow-md">
                                        ★ 5.0 (62 avis)
                                    </span>
                                </div>
                                <h3 className="font-serif text-2xl font-bold text-aegean mb-1">
                                    Mme. Aminata Kamara
                                </h3>
                                <p className="font-ui text-xs font-semibold text-gold mb-3 uppercase tracking-wider">
                                    Français, Histoire & Philosophie · Collège & BAC
                                </p>
                                <p className="font-ui text-sm text-stone-600 leading-relaxed">
                                    Agrégée de lettres, spécialiste de la préparation aux examens nationaux et concours.
                                </p>
                            </div>
                            <div className="mt-6 pt-4 border-t border-marble-stone flex items-center justify-between text-xs">
                                <span className="font-bold text-aegean">18 000 FCFA / h</span>
                                <span className="badge-gold">Super Répétiteur 🏆</span>
                            </div>
                        </div>

                        {/* Répétiteur 3 */}
                        <div className="card-areta p-6 flex flex-col justify-between">
                            <div>
                                <div className="relative rounded-lg overflow-hidden h-52 mb-5 bg-aegean-deep flex items-center justify-center p-6 text-center text-marble meander-section">
                                    <div>
                                        <div className="size-16 mx-auto rounded-full bg-gold/20 border-2 border-gold flex items-center justify-center font-display text-2xl font-bold text-gold mb-3">
                                            +140
                                        </div>
                                        <h4 className="font-serif text-lg font-bold text-marble">Répétiteurs Disponibles</h4>
                                        <p className="font-ui text-xs text-marble/70 mt-1">À Abidjan, Bouaké, Yamoussoukro</p>
                                    </div>
                                </div>
                                <h3 className="font-serif text-2xl font-bold text-aegean mb-1">
                                    Rejoignez le Réseau
                                </h3>
                                <p className="font-ui text-xs font-semibold text-gold mb-3 uppercase tracking-wider">
                                    Toutes Matières · Tous Niveaux
                                </p>
                                <p className="font-ui text-sm text-stone-600 leading-relaxed">
                                    Publiez votre besoin en 2 minutes ou postulez directement en tant qu'enseignant prestataire.
                                </p>
                            </div>
                            <div className="mt-6 pt-4 border-t border-marble-stone flex items-center justify-between text-xs">
                                <span className="font-bold text-aegean">Self-Service</span>
                                <Link href={register()} className="font-bold text-gold hover:underline font-display">
                                    S'inscrire ➔
                                </Link>
                            </div>
                        </div>

                    </div>
                </section>

                {/* ── LES 5 MODULES FONCTIONNELS ────────────────────────────────────────── */}
                <section id="modules" className="py-20 px-6 max-w-7xl mx-auto border-t border-marble-stone">
                    <div className="text-center mb-16">
                        <span className="badge-gold uppercase tracking-widest text-[11px] font-bold mb-3">
                            Décomposition Fonctionnelle
                        </span>
                        <h2 className="font-display text-3xl sm:text-4xl font-bold text-aegean mt-2">
                            Une Plateforme 100% Autonome
                        </h2>
                        <p className="font-serif text-lg text-stone-600 max-w-2xl mx-auto mt-3">
                            Interaction sans intermédiaire administrative avec garantie pédagogique.
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
                                    1. Place de Marché (Job Board)
                                </h3>
                                <p className="font-ui text-sm text-stone-600 leading-relaxed">
                                    Formulaire parent géolocalisé (Ville, Quartier, Matière, Fréquence). Flux d'offres réactif côté répétiteur avec filtrage dynamique instantané.
                                </p>
                            </div>
                            <div className="mt-6 pt-4 border-t border-marble-stone flex items-center justify-between text-xs text-stone-500 font-medium">
                                <span>Indépendance des Offres</span>
                                <span className="text-gold font-bold font-display">Matching →</span>
                            </div>
                        </div>

                        {/* Card 2: Core Tracker */}
                        <div className="card-areta p-8 flex flex-col justify-between">
                            <div>
                                <div className="size-12 rounded-lg bg-gold-pale text-bronze flex items-center justify-center text-2xl mb-6">
                                    📜
                                </div>
                                <h3 className="font-serif text-2xl font-bold text-aegean mb-3">
                                    2. Suivi Pédagogique (Core Tracker)
                                </h3>
                                <p className="font-ui text-sm text-stone-600 leading-relaxed">
                                    Après chaque cours, le répétiteur saisit la note sur 20 et le compte-rendu textuel. La planification suivante est bloquée tant que le rapport n'est pas soumis.
                                </p>
                            </div>
                            <div className="mt-6 pt-4 border-t border-marble-stone flex items-center justify-between text-xs text-stone-500 font-medium">
                                <span>Moyenne Temps Réel</span>
                                <span className="text-gold font-bold font-display">Blocage Sécurité →</span>
                            </div>
                        </div>

                        {/* Card 3: AI Evaluator */}
                        <div className="card-areta p-8 flex flex-col justify-between">
                            <div>
                                <div className="size-12 rounded-lg bg-terracotta-pale text-terracotta flex items-center justify-center text-2xl mb-6">
                                    🧠
                                </div>
                                <h3 className="font-serif text-2xl font-bold text-aegean mb-3">
                                    3. Évaluateur Pédagogique IA
                                </h3>
                                <p className="font-ui text-sm text-stone-600 leading-relaxed">
                                    Analyse automatisée via les LLM DeepSeek / OpenAI. Renvoie un JSON strict contenant : lacunes détectées, tendance de progression et plan d'action ciblé.
                                </p>
                            </div>
                            <div className="mt-6 pt-4 border-t border-marble-stone flex items-center justify-between text-xs text-stone-500 font-medium">
                                <span>Worker & Job Queue</span>
                                <span className="text-terracotta font-bold font-display">JSON Mode →</span>
                            </div>
                        </div>

                    </div>
                </section>

                {/* ── STEPPER : PROCESSUS EN 4 ÉTAPES ─────────────────────────────────── */}
                <section className="py-20 bg-marble-dark/40 border-y border-marble-stone meander-section">
                    <div className="max-w-7xl mx-auto px-6">
                        <div className="text-center mb-16">
                            <h2 className="font-display text-3xl sm:text-4xl font-bold text-aegean">
                                Le Flux de Données
                            </h2>
                            <p className="font-serif text-lg text-stone-600 mt-2">
                                De la publication du besoin à la restitution du bilan visuel.
                            </p>
                        </div>

                        <div className="grid md:grid-cols-4 gap-6">

                            <div className="bg-marble-warm p-6 rounded-lg border border-marble-stone relative shadow-marble">
                                <span className="font-display text-4xl font-bold text-gold/30 absolute top-4 right-4">01</span>
                                <h4 className="font-ui text-base font-bold text-aegean mb-2">1. Parent crée l'offre</h4>
                                <p className="font-ui text-xs text-stone-600">
                                    Définition de la matière, quartier, fréquence et objectif de révision.
                                </p>
                            </div>

                            <div className="bg-marble-warm p-6 rounded-lg border border-marble-stone relative shadow-marble">
                                <span className="font-display text-4xl font-bold text-gold/30 absolute top-4 right-4">02</span>
                                <h4 className="font-ui text-base font-bold text-aegean mb-2">2. Match & Contrat</h4>
                                <p className="font-ui text-xs text-stone-600">
                                    Acceptation du répétiteur et génération du Contrat Pédagogique virtuel.
                                </p>
                            </div>

                            <div className="bg-marble-warm p-6 rounded-lg border border-marble-stone relative shadow-marble">
                                <span className="font-display text-4xl font-bold text-gold/30 absolute top-4 right-4">03</span>
                                <h4 className="font-ui text-base font-bold text-aegean mb-2">3. Note & Commentaire</h4>
                                <p className="font-ui text-xs text-stone-600">
                                    Saisie à la fin du cours. Stockage backend et mise à jour de la moyenne.
                                </p>
                            </div>

                            <div className="bg-marble-warm p-6 rounded-lg border border-marble-stone relative shadow-marble">
                                <span className="font-display text-4xl font-bold text-gold/30 absolute top-4 right-4">04</span>
                                <h4 className="font-ui text-base font-bold text-aegean mb-2">4. Bilan & Alertes IA</h4>
                                <p className="font-ui text-xs text-stone-600">
                                    Cron Job / Worker déclenche l'IA. Restitution en alertes visuelles sur les dashboards.
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
                            <a href="/docs/api" className="text-gold hover:underline font-semibold font-display">
                                API REST OpenAPI (v1)
                            </a>
                        </div>
                    </div>
                </footer>

            </div>
        </>
    );
}
