<?php

namespace App\Controller;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Ottosmops\Pdftotext\Extract;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Trinetus\PayBySquareGenerator\PayBySquareGenerator;

#[Route('/qr', name: 'qr.')]
class QrController extends AbstractController {
    #[Route('', name: 'index')]
    public function index(Request $request): Response {
        $form = $this->createFormBuilder()
            ->add('pdf', FileType::class, [
                'constraints' => [
                    new File([
                        'maxSize' => '16m',
                        'mimeTypes' => ['application/pdf', 'application/x-pdf'],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ]
            ])
            ->add('submit', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $pdfFile */
            $pdfFile = $form->get('pdf')->getData();
            $text = (new Extract())->pdf($pdfFile->getRealPath())->text();


            $ibanRegex = '[A-Z]{2}\\d{22}';
            $bicRegex = '[A-Z]{6}';
            $constantRegex = '\\d{4}';
            $specificRegex = '\\d{6}';
            $variableRegex = '\\d{7,}';
            $amountRegex = '\\d+\\.\\d+';

            $ibanRegexp = '/[A-Z]{2}[0-9]{22}/';
            $match = [];
            preg_match($ibanRegexp, $text, $match);
            $index = strpos($text, $match[0]);
            $text = substr($text, $index);
            $parts = preg_split('/\\s+/', $text);
            $parts = array_filter(
                $parts,
                fn($part) => preg_match_all("/$ibanRegex|\\d+(\\.\\d+)?|[A-Z]{8}/", $part)
            );

            $transactions = [];
            foreach ($parts as $part) {
                if (preg_match_all("/$ibanRegex/", $part)) {
                    $transactions[] = ['iban' => $part];
                } else if (preg_match_all("/$bicRegex/", $part)) {
                    $transactions[count($transactions) - 1]['bic'] = $part;
                } else if (preg_match_all("/$constantRegex/", $part)) {
                    $transactions[count($transactions) - 1]['cs'] = $part;
                } else if (preg_match_all("/$specificRegex/", $part)) {
                    $transactions[count($transactions) - 1]['ss'] = $part;
                } else if (preg_match_all("/$variableRegex/", $part)) {
                    $transactions[count($transactions) - 1]['vs'] = $part;
                } else if (preg_match_all("/$amountRegex/", $part)) {
                    $transactions[count($transactions) - 1]['amount'] = (float) $part;
                }
            }

            $writer = new PngWriter();

            $qrCodes = [];
            foreach ($transactions as $transaction) {
                $outputString = (new PayBySquareGenerator())
                    ->setAmount($transaction['amount'] ?: 0)
                    ->setIban($transaction['iban'])
                    ->setBic($transaction['bic'])
                    ->setVariableSymbol(empty($transaction['vs']) ? '' : $transaction['vs'])
                    ->setConstantSymbol(empty($transaction['cs']) ? '' : $transaction['cs'])
                    ->setSpecificSymbol(empty($transaction['ss']) ? '' : $transaction['ss'])
                    ->getOutput();
                // Create QR code
                $qrCode = QrCode::create('Life is too short to be generating QR codes')
                    ->setEncoding(new Encoding('UTF-8'))
                    ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
                    ->setSize(300)
                    ->setMargin(10)
                    ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
                    ->setForegroundColor(new Color(0, 0, 0))
                    ->setBackgroundColor(new Color(255, 255, 255));
                $label = Label::create($transaction['amount'])
                    ->setTextColor(new Color(255, 0, 0));

                $transaction['qr'] = $writer->write($qrCode, null, $label);
            }

            echo '<pre>';
            var_dump($transactions);
            die;

            return $this->redirectToRoute('qr.index');
        }

        return $this->render('qr/index.html.twig', ['form' => $form]);
    }
}
