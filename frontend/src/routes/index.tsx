import { createFileRoute } from "@tanstack/react-router";
import { LandingNavbar } from "@/components/landing/navbar";
import { LandingHero } from "@/components/landing/hero";
import { LandingTrustedBy } from "@/components/landing/trusted-by";
import { LandingHowItWorks } from "@/components/landing/how-it-works";
import { LandingTemplateGallery } from "@/components/landing/template-gallery";
import { LandingFeatures } from "@/components/landing/features";
import { LandingInteractiveDemo } from "@/components/landing/interactive-demo";
import { LandingPricing } from "@/components/landing/pricing";
import { LandingTestimonials } from "@/components/landing/testimonials";
import { LandingFaq } from "@/components/landing/faq";
import { LandingBlog } from "@/components/landing/blog";
import { LandingFinalCta } from "@/components/landing/final-cta";
import { LandingFooter } from "@/components/landing/footer";

const title = "Undang Akoe — Buat Undangan Pernikahan Online dalam Hitungan Menit";
const description =
  "Buat undangan digital pernikahan, ulang tahun, dan acara lainnya. Pilih tema, isi data, lalu bagikan satu tautan ke semua tamu — lengkap dengan RSVP, buku tamu, musik, dan amplop digital.";

const schema = {
  "@context": "https://schema.org",
  "@type": "WebSite",
  name: "Undang Akoe",
  url: "https://undangakoe.test",
  potentialAction: {
    "@type": "SearchAction",
    target: "https://undangakoe.test/?q={search_term_string}",
    "query-input": "required name=search_term_string",
  },
};

export const Route = createFileRoute("/")({
  head: () => ({
    meta: [
      { title },
      { name: "description", content: description },
      { property: "og:title", content: title },
      { property: "og:description", content: description },
      { property: "og:type", content: "website" },
      { name: "twitter:card", content: "summary_large_image" },
      { name: "twitter:title", content: title },
      { name: "twitter:description", content: description },
    ],
  }),
  component: LandingPage,
});

function LandingPage() {
  return (
    <main className="font-landing-sans">
      {/* eslint-disable-next-line react/no-danger */}
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(schema) }}
      />
      <LandingNavbar />
      <LandingHero />
      <LandingTrustedBy />
      <LandingHowItWorks />
      <LandingTemplateGallery />
      <LandingFeatures />
      <LandingInteractiveDemo />
      <LandingPricing />
      <LandingTestimonials />
      <LandingFaq />
      <LandingBlog />
      <LandingFinalCta />
      <LandingFooter />
    </main>
  );
}
