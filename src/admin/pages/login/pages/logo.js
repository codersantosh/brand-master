/* WordPress */
import { __ } from '@wordpress/i18n';
import { useContext, useMemo } from '@wordpress/element';

/* Library */
import classNames from 'classnames';

import { cloneDeep, isEmpty } from 'lodash';

/*Atrc*/
import {
	AtrcText,
	AtrcControlToggle,
	AtrcWireFrameContentSidebar,
	AtrcWireFrameHeaderContentFooter,
	AtrcPrefix,
	AtrcPanelBody,
	AtrcPanelRow,
	AtrcContent,
	AtrcTitleTemplate1,
	AtrcControlFile,
	AtrcNestedObjUpdateByKey2,
	AtrcControlText,
} from 'atrc';

/* Inbuilt */
import { AtrcReduxContextData } from '../../../routes';
import { DocsTitle } from '../../../components/molecules';

/*Local*/
const MainContent = () => {
	const data = useContext(AtrcReduxContextData);

	const { dbSettings, dbUpdateSetting } = data;

	const { login = {} } = dbSettings;
	const { logo = {} } = login;
	const { on: loginOn = false, img = {}, text = '', url = '' } = logo;

	/* map value onf file control */
	const mapImgValue = useMemo(() => {
		const imgVal = {};

		if (!isEmpty(img)) {
			if (img.frm) {
				imgVal.frm = img.frm;
			}
			imgVal.data = [];
			if (img.id || img.url) {
				imgVal.data[0] = {};
				if (img.id) {
					imgVal.data[0].id = img.id;
				}
				if (img.url) {
					imgVal.data[0].url = img.url;
				}
			}
		}
		return imgVal;
	}, [img]);

	return (
		<AtrcContent>
			<AtrcPanelRow>
				<AtrcControlToggle
					label={__('Add own logo on login page', 'brand-master')}
					checked={loginOn}
					onChange={() => {
						const updatedSettings = AtrcNestedObjUpdateByKey2({
							settings: login,
							key1: 'logo',
							key2: 'on',
							val2: !loginOn,
						});
						dbUpdateSetting('login', updatedSettings);
					}}
				/>
			</AtrcPanelRow>
			{loginOn ? (
				<>
					<AtrcPanelRow>
						<AtrcPanelBody
							className={classNames('at-flx-grw-1')}
							title={__('Logo', 'brand-master')}
							initialOpen={true}>
							<AtrcPanelRow>
								<AtrcControlFile
									value={mapImgValue}
									onChange={(newVal) => {
										const requiredVal = {};
										if (newVal.frm) {
											requiredVal.frm = newVal.frm;
										}
										console.log(newVal);
										if (newVal.data && newVal.data[0]) {
											const newImg = newVal.data[0];
											requiredVal.id = newImg.id;
											requiredVal.url = newImg.url;
											requiredVal.alt = newImg.title;
											requiredVal.ttl = newImg.title;
											requiredVal.w = newImg.w;
											requiredVal.h = newImg.h;
										}

										const updatedSettings = AtrcNestedObjUpdateByKey2({
											settings: login,
											key1: 'logo',
											key2: 'img',
											val2: requiredVal,
										});
										dbUpdateSetting('login', updatedSettings);
									}}
									selectSourceProps={{
										label: __('Image source', 'brand-master'),
									}}
									addButtonProps={{
										text: __('Add logo', 'brand-master'),
									}}
									removeButtonProps={{
										text: __('Remove logo', 'brand-master'),
									}}
									frameProps={{
										title: __('Select logo from images', 'atrc-prefix-atrc'),
										button: {
											text: __('Select logo', 'atrc-prefix-atrc'),
										},
										multiple: false,
										library: {
											type: 'image',
										},
									}}
								/>
							</AtrcPanelRow>
							<AtrcPanelRow className={classNames('at-m')}>
								<AtrcControlText
									label={__('Logo text', 'brand-master')}
									value={text}
									onChange={(newVal) => {
										const updatedSettings = AtrcNestedObjUpdateByKey2({
											settings: login,
											key1: 'logo',
											key2: 'text',
											val2: newVal,
										});
										dbUpdateSetting('login', updatedSettings);
									}}
								/>
							</AtrcPanelRow>
							<AtrcPanelRow className={classNames('at-m')}>
								<AtrcControlText
									label={__('Logo link', 'brand-master')}
									value={url}
									type='url'
									onChange={(newVal) => {
										const updatedSettings = AtrcNestedObjUpdateByKey2({
											settings: login,
											key1: 'logo',
											key2: 'url',
											val2: newVal,
										});
										dbUpdateSetting('login', updatedSettings);
									}}
								/>
							</AtrcPanelRow>
						</AtrcPanelBody>
					</AtrcPanelRow>
				</>
			) : null}
		</AtrcContent>
	);
};

const Documentation = () => {
	const data = useContext(AtrcReduxContextData);

	const { lsSettings, lsSaveSettings } = data;

	return (
		<AtrcWireFrameHeaderContentFooter
			headerRowProps={{
				className: classNames(AtrcPrefix('header-docs'), 'at-m'),
			}}
			renderHeader={
				<DocsTitle
					onClick={() => {
						const localStorageClone = cloneDeep(lsSettings);
						localStorageClone.bmLlDocs1 = !localStorageClone.bmLlDocs1;
						lsSaveSettings(localStorageClone);
					}}
				/>
			}
			renderContent={
				<>
					<AtrcPanelBody
						className={classNames(AtrcPrefix('m-0'))}
						title={__(
							"How can I customize the appearance of my WordPress login page's logo?",
							'brand-master'
						)}
						initialOpen={true}>
						<AtrcText
							tag='p'
							className={classNames(AtrcPrefix('m-0'), 'at-m')}>
							{__(
								'The logo settings feature allows you to modify the logo image, logo text, and the link associated with the logo displayed on your WordPress login page.',
								'brand-master'
							)}
						</AtrcText>
					</AtrcPanelBody>
				</>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

const Settings = () => {
	const data = useContext(AtrcReduxContextData);
	const { lsSettings } = data;

	const { bmLlDocs1 } = lsSettings;

	return (
		<AtrcWireFrameHeaderContentFooter
			wrapProps={{
				className: classNames(AtrcPrefix('bg-white'), 'at-bg-cl'),
			}}
			renderHeader={
				<AtrcTitleTemplate1
					title={__('WordPress login page settings', 'brand-master')}
				/>
			}
			renderContent={
				<AtrcWireFrameContentSidebar
					wrapProps={{
						allowContainer: true,
						type: 'fluid',
						tag: 'section',
					}}
					renderContent={<MainContent />}
					renderSidebar={!bmLlDocs1 ? <Documentation /> : null}
					contentProps={{
						contentCol: bmLlDocs1 ? 'at-col-12' : 'at-col-7',
					}}
					sidebarProps={{
						sidebarCol: 'at-col-5',
					}}
				/>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

export default Settings;
