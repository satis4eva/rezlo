<?php
$co = config('company');
$addr = implode(', ', array_slice($co['address'], 0, 4));
?>
<p>These terms apply to every sale of goods by <?= e($co['name']) ?> through rezloltd.co.uk, by phone, by email or in person. Please read them before you order. Installation and other services are covered by our separate <a href="/services-terms">terms for installation and support services</a>.</p>

<h2 id="s1">1. About us</h2>
<p>1.1 We are <?= e(company_line()) ?> You can contact us on <?= e($co['phone']) ?> or at <a href="mailto:<?= e($co['email']) ?>"><?= e($co['email']) ?></a>.</p>
<p>1.2 In these terms, “we”, “us” and “our” mean <?= e($co['name']) ?>. “You” means the business buying goods from us. “Goods” means the products described in your order. “Contract” means the contract between you and us for the sale of goods.</p>

<h2 id="s2">2. Trade sales only</h2>
<p>2.1 Our website and prices are for businesses buying in the course of their trade, profession or business. By opening a trade account or placing an order, you confirm you are acting for a business and that the person ordering has authority to bind that business.</p>
<p>2.2 If, despite this, we sell to you as a consumer, clause 17 applies and nothing in these terms affects your statutory rights as a consumer.</p>

<h2 id="s3">3. Trade accounts</h2>
<p>3.1 You need an approved trade account to order online. We may ask for evidence of trading, such as a company number, VAT number or trade references, and we may carry out identity and credit checks.</p>
<p>3.2 We can decline an application, or suspend or close an account, at our discretion, including if a payment is overdue or we reasonably suspect fraud or misuse.</p>
<p>3.3 You are responsible for keeping your login details secure and for all orders placed using your account. Tell us straight away if you think someone else has used it.</p>
<p>3.4 Credit terms are only available if we agree them in writing. We set and may change or withdraw your credit limit at any time.</p>

<h2 id="s4">4. How the contract is formed</h2>
<p>4.1 Your order is an offer to buy the goods on these terms. The contract is formed when we send you an email confirming your order, or when we dispatch the goods, whichever happens first.</p>
<p>4.2 We may decline an order, including where goods are unavailable, a price or description is clearly wrong, or delivery to your address isn't possible. If we decline an order after taking payment, we will refund you in full.</p>
<p>4.3 These terms apply to the exclusion of any terms you try to impose, including terms on your purchase order, unless a director of ours agrees otherwise in writing.</p>
<p>4.4 Quotations are valid for 30 days unless they say otherwise and are not offers capable of acceptance until we confirm the order.</p>

<h2 id="s5">5. The goods</h2>
<p>5.1 Product photos, swatches and colour samples on screen are for illustration only. Natural materials such as wood and stone vary, and manufactured products such as tiles, flooring, wall coverings and paint can vary in shade, texture and size between production batches.</p>
<p>5.2 You are responsible for ordering enough goods for the job, including a reasonable allowance for cutting and waste, in one order so they come from the same batch where possible. We can't guarantee that goods ordered later will match an earlier batch.</p>
<p>5.3 You are responsible for checking that goods are suitable for your intended use and installation conditions. Follow the manufacturer's instructions on storage, acclimatisation, preparation and installation.</p>
<p>5.4 We may make minor changes to the specification of goods to meet legal requirements, or substitute goods of equivalent or better quality, if this doesn't materially affect their quality or performance.</p>

<h2 id="s6">6. Prices and price breaks</h2>
<p>6.1 Prices are in pounds sterling and exclude VAT, which we add at the rate in force on the date of supply. Delivery charges are shown separately at checkout.</p>
<p>6.2 Bulk price breaks apply per product line in a single order, based on the quantity of that line. The price shown in your order confirmation is the price you pay.</p>
<p>6.3 Some products are sold only in minimum quantities or in set multiples. We will round quantities up to meet these before you confirm your order.</p>
<p>6.4 We may change prices at any time before the contract is formed. If we discover an obvious pricing error after you order, we will contact you, and you may confirm the order at the correct price or cancel it for a full refund.</p>

<h2 id="s7">7. Payment</h2>
<p>7.1 Unless we have agreed credit terms with you, you must pay for your order in full before we dispatch it, by card or by bank transfer.</p>
<p>7.2 Card payments are processed by our payment provider DPayments on its secure payment page. We do not see or store your full card details.</p>
<p>7.3 If you have a credit account, we will invoice you on or after dispatch and you must pay within 30 days of the invoice date, without any deduction or set-off, unless we have agreed different terms in writing.</p>
<p>7.4 If you pay late, we may charge interest and compensation under the Late Payment of Commercial Debts (Interest) Act 1998, currently interest at 8% a year above the Bank of England base rate plus fixed compensation for recovery costs. We may also suspend deliveries and other orders until you have paid all overdue amounts.</p>
<p>7.5 Time for payment is of the essence of the contract.</p>

<h2 id="s8">8. Delivery</h2>
<p>8.1 We deliver to addresses in mainland Great Britain. Delivery to the Scottish Highlands and islands, Northern Ireland, the Isle of Man, the Isles of Scilly and the Channel Islands is by quotation only. Full details are in our <a href="/delivery">delivery policy</a>, which forms part of these terms.</p>
<p>8.2 Delivery dates are estimates. We will do our best to meet them, but time for delivery is not of the essence and we are not liable for losses caused by delay unless we have agreed a guaranteed delivery date in writing.</p>
<p>8.3 Deliveries are kerbside or to the nearest safe, accessible point a vehicle can reach. You must provide safe access, suitable for heavy goods vehicles where needed, and enough people or equipment to unload. If a delivery can't be made because of you, we may charge for redelivery and storage.</p>
<p>8.4 We may deliver an order in instalments. Each instalment is a separate contract, and a problem with one doesn't entitle you to cancel the others.</p>

<h2 id="s9">9. Checking your delivery</h2>
<p>9.1 Check the goods when they arrive. If the packaging is damaged, sign for the delivery as “damaged” and describe the damage on the driver's paperwork.</p>
<p>9.2 You must tell us about any damage, shortage or wrong item within 48 hours of delivery, with photos where possible. If you don't, the goods are treated as delivered in good condition and in the correct quantity, except for defects that couldn't reasonably have been found on inspection.</p>
<p>9.3 Do not install goods that are visibly damaged, defective, the wrong shade or batch, or not what you ordered. Installing goods is treated as acceptance of any defect that was visible before installation.</p>

<h2 id="s10">10. Risk and ownership</h2>
<p>10.1 Risk in the goods passes to you when they are delivered, or when we tried to deliver them if you failed to take delivery.</p>
<p>10.2 Ownership of the goods does not pass to you until we have received payment in full for those goods and for any other goods or services we have supplied to you that are due for payment.</p>
<p>10.3 Until ownership passes, you must store the goods separately and identifiably as our property, keep them insured, and not remove any identifying marks. You may use or resell the goods in the ordinary course of your business, but if you become insolvent or fail to pay on time, your right to do so ends immediately.</p>
<p>10.4 If ownership has not passed and payment is overdue, we may require you to return the goods, and you give us and our agents permission to enter any premises where the goods are kept to recover them.</p>

<h2 id="s11">11. Defective goods</h2>
<p>11.1 We warrant that on delivery, and for 12 months afterwards or any longer manufacturer's warranty period we pass on to you, the goods will conform to their description and be free from material defects in materials and manufacture.</p>
<p>11.2 This warranty doesn't cover defects caused by fair wear and tear, damage after delivery, incorrect storage or acclimatisation, unsuitable subfloors or substrates, moisture, failure to follow the manufacturer's instructions or good trade practice, or alterations you make.</p>
<p>11.3 If you think goods are defective, tell us in writing as soon as you notice, give us a reasonable opportunity to inspect them, and don't use or install them further. If the goods are defective, we will, at our option, repair or replace them or refund the price paid for the defective goods.</p>
<p>11.4 Except as set out in this clause 11, and to the extent the law allows, all terms implied by sections 13 to 15 of the Sale of Goods Act 1979 are excluded.</p>

<h2 id="s12">12. Returning goods you don't need</h2>
<p>12.1 We accept returns of unwanted goods only by prior agreement and on the conditions in our <a href="/returns">returns and refunds policy</a>, which forms part of these terms.</p>

<h2 id="s13">13. Cancelling an order</h2>
<p>13.1 You may cancel an order before we dispatch it by contacting us. If the order includes goods ordered specially for you, cut to size, mixed or tinted, or not normally stocked, we may charge you for any costs we can't recover from our supplier.</p>
<p>13.2 We may cancel a contract, or suspend deliveries, if you fail to pay on time, become insolvent or enter any insolvency procedure, or stop trading. We will refund any amount you have paid for goods not delivered, less any amount you owe us.</p>

<h2 id="s14">14. Our liability</h2>
<p>14.1 Nothing in these terms limits or excludes our liability for death or personal injury caused by our negligence, fraud or fraudulent misrepresentation, breach of the terms implied by section 12 of the Sale of Goods Act 1979, or any other liability that cannot be limited or excluded by law.</p>
<p>14.2 Subject to clause 14.1, we are not liable for any loss of profit, loss of revenue, loss of business or contracts, loss of goodwill, liquidated damages or delay penalties under your contracts with others, or any indirect or consequential loss.</p>
<p>14.3 Subject to clause 14.1, unless we have agreed otherwise in writing, we are not liable for the cost of uplifting, removing, refitting or reinstalling goods, or for labour costs.</p>
<p>14.4 Subject to clause 14.1, our total liability to you arising from or in connection with a contract, whether in contract, tort (including negligence), breach of statutory duty or otherwise, is limited to the price paid for the goods under that contract.</p>

<h2 id="s15">15. Events outside our control</h2>
<p>15.1 We are not liable for any delay or failure to perform caused by events outside our reasonable control, including supplier or manufacturer failure, transport disruption, extreme weather, fire, flood, strikes, pandemics, war, or government action. If such an event lasts more than 30 days, either of us may cancel the affected order by written notice, and we will refund any payment for goods not delivered.</p>

<h2 id="s16">16. Your information</h2>
<p>16.1 We use your personal information as described in our <a href="/privacy">privacy policy</a>. If you have a credit account, we may share information with credit reference agencies.</p>

<h2 id="s17">17. If you are a consumer</h2>
<p>17.1 If you are buying as a consumer, you have legal rights in relation to goods that are faulty or not as described, and these terms do not affect those rights. Advice about your legal rights is available from Citizens Advice.</p>
<p>17.2 For goods bought online or by phone, you can usually cancel the contract within 14 days of receiving the goods without giving a reason, under the Consumer Contracts (Information, Cancellation and Additional Charges) Regulations 2013. To cancel, tell us clearly in writing, for example by email to <?= e($co['email']) ?>. You must return the goods within 14 days of cancelling, at your own cost, unused and in their original condition. We will refund the price and standard delivery charge within 14 days of receiving the goods back or evidence that you have sent them, and may reduce the refund if the goods have been handled more than necessary to inspect them.</p>
<p>17.3 The right to cancel does not apply to goods made to your specification or clearly personalised, such as paint mixed to a colour you chose or flooring or wall covering cut to the length you ordered, or to sealed goods unsealed after delivery that are not suitable for return for hygiene or safety reasons.</p>
<p>17.4 Clauses 9.2, 11.4 and 14.2 to 14.4 do not apply to consumers. We are responsible to consumers for foreseeable loss and damage caused by our breach of these terms or our failure to use reasonable care and skill.</p>

<h2 id="s18">18. General</h2>
<p>18.1 You may not transfer your rights under a contract without our written consent. We may transfer our rights to another business.</p>
<p>18.2 Notices must be in writing and sent to the address or email address of the other party most recently notified. Notices to us should go to <?= e($co['email']) ?> or <?= e($addr) ?>.</p>
<p>18.3 If any part of these terms is found to be invalid or unenforceable, the rest remains in force.</p>
<p>18.4 If we don't enforce a right straight away, we can still enforce it later.</p>
<p>18.5 No one other than you and us has any right to enforce these terms under the Contracts (Rights of Third Parties) Act 1999.</p>
<p>18.6 Each contract, together with these terms and the policies they refer to, is the whole agreement between us about its subject matter. You confirm you have not relied on any statement that is not set out in it.</p>
<p>18.7 We may update these terms from time to time. The version on our website when you place your order applies to that order.</p>
<p>18.8 These terms and any dispute arising from them, including non-contractual disputes, are governed by the law of England and Wales, and the courts of England and Wales have exclusive jurisdiction. If you are a consumer living in Scotland or Northern Ireland, you may also bring proceedings in your local courts.</p>
