import { useEffect, useState } from "react";
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/ui/accordion";
import { api } from "@/lib/api";

interface PublicFaq {
  id: number;
  question: string;
  answer: string;
  category: string | null;
}

export function LandingFaq() {
  const [faqs, setFaqs] = useState<PublicFaq[]>([]);

  useEffect(() => {
    api
      .get<{ data: PublicFaq[] }>("/public/faqs")
      .then((res) => setFaqs(res.data))
      .catch(() => setFaqs([]));
  }, []);

  if (faqs.length === 0) return null;

  return (
    <section id="faq" className="bg-muted/30 py-20 sm:py-28">
      <div className="mx-auto max-w-3xl px-4 sm:px-6">
        <div className="text-center">
          <p className="font-landing-sans text-sm font-medium text-brand">FAQ</p>
          <h2 className="mt-2 font-landing-display text-3xl font-bold tracking-tight sm:text-4xl">
            Pertanyaan yang Sering Diajukan
          </h2>
        </div>

        <Accordion type="single" collapsible className="mt-10 rounded-xl border bg-card px-6">
          {faqs.map((faq) => (
            <AccordionItem key={faq.id} value={String(faq.id)}>
              <AccordionTrigger className="font-landing-sans text-left font-semibold">
                {faq.question}
              </AccordionTrigger>
              <AccordionContent className="font-landing-sans leading-relaxed text-muted-foreground">
                {faq.answer}
              </AccordionContent>
            </AccordionItem>
          ))}
        </Accordion>
      </div>
    </section>
  );
}
